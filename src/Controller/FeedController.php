<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommentReactions;
use App\Entity\Follows;
use App\Entity\PostReactions;
use App\Entity\Posts;
use App\Entity\SavedPosts;
use App\Entity\Tags;
use App\Entity\Users;
use App\Entity\UserTopic;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FeedController extends AbstractController
{

    public function feed(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $type = $request->query->get('type', 'mixed'); // mixed | following | interests
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, (int)$request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        $qb = $em->createQueryBuilder();

        $qb->select('DISTINCT p', 'u')
            ->from(Posts::class, 'p')
            ->join('p.user', 'u')
            ->where('p.deletedAt IS NULL')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($type === 'following') {
            $qb->andWhere(
                $qb->expr()->orX(
                    'p.user = :me',
                    'p.user IN (
                    SELECT IDENTITY(f.followed)
                    FROM App\Entity\Follows f
                    WHERE f.follower = :me
                )'
                )
            )->setParameter('me', $user);

        } elseif ($type === 'interests') {

            $qb->join('p.tag', 't')
                ->andWhere(
                    't.id IN (
                        SELECT IDENTITY(ut.tag)
                        FROM App\Entity\UserTopic ut
                        WHERE ut.user = :me
                    )'
                )
                ->setParameter('me', $user);

        } elseif ($type === 'mixed') {

            $qb->leftJoin('p.tag', 't')
                ->andWhere(
                    $qb->expr()->orX(
                        'p.user = :me',
                        'p.user IN (
                           SELECT IDENTITY(f.followed)
                           FROM App\Entity\Follows f
                           WHERE f.follower = :me
                       )',
                        't.id IN (
                           SELECT IDENTITY(ut.tag)
                           FROM App\Entity\UserTopic ut
                           WHERE ut.user = :me
                       )'
                    )
                )
                ->setParameter('me', $user);

        } else {
            return $this->json([
                'message' => 'type no válido (following, interests, mixed)'
            ], 400);
        }

        $posts = $qb->getQuery()->getResult();

        $data = [];

        foreach ($posts as $post) {
            $isSaved = $em->getRepository(SavedPosts::class)
                    ->findOneBy([
                        'user' => $user,
                        'post' => $post
                    ]) !== null;

            $reactionsCount = $em->createQueryBuilder()
                ->select('COUNT(pr.id)')
                ->from(PostReactions::class, 'pr')
                ->where('pr.post = :post')
                ->setParameter('post', $post)
                ->getQuery()
                ->getSingleScalarResult();

            $commentsCount = $em->createQueryBuilder()
                ->select('COUNT(cr.id)')
                ->from(CommentReactions::class, 'cr')
                ->join('cr.comment', 'c')
                ->where('c.post = :post')
                ->andWhere('c.deletedAt IS NULL')
                ->setParameter('post', $post)
                ->getQuery()
                ->getSingleScalarResult();

            $data[] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'created_at' => $post->getCreatedAt()
                    ? $post->getCreatedAt()->format('Y-m-d H:i:s')
                    : null,
                'is_saved' => $isSaved,
                'reactions_count' => (int)$reactionsCount,
                'comments_count' => (int)$commentsCount,

                'user' => [
                    'id' => $post->getUser()->getId(),
                    'username' => $post->getUser()->getUsernameUser(),
                    'profileImageUrl' => method_exists($post->getUser(), 'getProfileImageUrl')
                        ? $post->getUser()->getProfileImageUrl()
                        : null
                ]
            ];
        }
    
        return $this->json([
            'type' => $type,
            'page' => $page,
            'limit' => $limit,
            'count' => count($data),
            'posts' => $data
        ]);
    }

    public function postExplore(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, max(1, (int)$request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $q = trim((string)$request->query->get('q', ''));

        $currentUser = $this->getUser();

        $result = [
            'page' => $page,
            'limit' => $limit,
            'query' => $q ?: null,
            'posts' => [],
            'users' => [],
            'tags' => [],
            'posts_count' => 0,
            'users_count' => 0,
            'tags_count' => 0,
        ];

        if ($q === '') {
            return $this->json($result);
        }

        $postQb = $em->createQueryBuilder();

        $postQb->select('DISTINCT p')
            ->from(Posts::class, 'p')
            ->join('p.user', 'u')
            ->join('p.tag', 't')
            ->where('p.deletedAt IS NULL')
            ->andWhere('t.name LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $posts = $postQb->getQuery()->getResult();

        foreach ($posts as $post) {
            $isSaved = false;

            if ($currentUser) {
                $savedPost = $em->getRepository(SavedPosts::class)->findOneBy([
                    'post' => $post,
                    'user' => $currentUser,
                ]);

                $isSaved = $savedPost !== null;
            }

            $reactionsCount = 0;
            $commentsCount = 0;

            if (method_exists($post, 'getReactions')) {
                $reactionsCount = count($post->getReactions());
            }

            if (method_exists($post, 'getComments')) {
                $commentsCount = count($post->getComments());
            }

            $result['posts'][] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                'is_saved' => $isSaved,
                'reactions_count' => $reactionsCount,
                'comments_count' => $commentsCount,
                'user' => [
                    'id' => $post->getUser()->getId(),
                    'username' => method_exists($post->getUser(), 'getUsernameUser')
                        ? $post->getUser()->getUsernameUser()
                        : $post->getUser()->getUsername(),
                    'profileImageUrl' => method_exists($post->getUser(), 'getProfileImageUrl')
                        ? $post->getUser()->getProfileImageUrl()
                        : null,
                ],
            ];
        }

        $userQb = $em->createQueryBuilder();

        $userQb->select('u')
            ->from(Users::class, 'u')
            ->where('u.name LIKE :q')
            ->orWhere('u.username LIKE :q')
            ->orWhere('u.email LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $users = $userQb->getQuery()->getResult();

        foreach ($users as $user) {
            if ($currentUser && $user->getId() === $currentUser->getId()) {
                continue;
            }

            $isFollowing = false;

            if ($currentUser) {
                $follow = $em->getRepository(Follows::class)->findOneBy([
                    'follower' => $currentUser,
                    'followed' => $user,
                ]);

                $isFollowing = $follow !== null;
            }

            $result['users'][] = [
                'id' => $user->getId(),
                'name' => method_exists($user, 'getName') ? $user->getName() : null,
                'username' => method_exists($user, 'getUsernameUser')
                    ? $user->getUsernameUser()
                    : $user->getUsername(),
                'profileImageUrl' => method_exists($user, 'getProfileImageUrl')
                    ? $user->getProfileImageUrl()
                    : null,
                'bio' => method_exists($user, 'getBio')
                    ? $user->getBio()
                    : null,
                'isVerified' => method_exists($user, 'getIsVerified')
                    ? $user->getIsVerified()
                    : false,
                'isFollowing' => $isFollowing,
            ];
        }

        $tagQb = $em->createQueryBuilder();

        $tagQb->select('t')
            ->from(Tags::class, 't')
            ->where('t.name LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('t.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $tags = $tagQb->getQuery()->getResult();

        foreach ($tags as $tag) {
            $isFollowingTag = false;

            if ($currentUser) {
                $userTopic = $em->getRepository(UserTopic::class)
                    ->findOneBy([
                        'user' => $currentUser,
                        'tag' => $tag,
                    ]);

                $isFollowingTag = $userTopic !== null;
            }

            $postsCount = 0;

            if (method_exists($tag, 'getPosts')) {
                $postsCount = count($tag->getPosts());
            }

            $result['tags'][] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'description' => method_exists($tag, 'getDescription')
                    ? $tag->getDescription()
                    : null,
                'posts_count' => $postsCount,
                'isFollowing' => $isFollowingTag,
            ];
        }

        $result['posts_count'] = count($result['posts']);
        $result['users_count'] = count($result['users']);
        $result['tags_count'] = count($result['tags']);

        return $this->json($result);
    }
}
