<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\PostMedia;
use App\Entity\PostReactions;
use App\Entity\Posts;
use App\Entity\SavedPosts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractController
{

    public function createPost(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json([
                'message' => 'JSON invalido.',
                400
            ]);
        }

        if ($request->isMethod('POST')) {
            $content = $data['content'];
            $visibility = $data['visibility'];
            $location_name = $data['location_name'];

            $newPost = new Posts();
            $newPost->setuser($user);
            $newPost->setcontent($content);
            $newPost->setlocationName($location_name);
            $newPost->setvisibility($visibility);
            $newPost->setStatus('published');
            $newPost->setIsAd(0);
            $newPost->setReactionCount(0);
            $newPost->setCommentCount(0);
            $newPost->setSaveCount(0);
            $newPost->setCreatedAt(new \DateTime('now'));
            $newPost->setUpdatedAt(new \DateTime('now'));

            $em->persist($newPost);
            $em->flush();

        }

        return $this->json([
            'message' => 'El post creado con exito.',
            'data' => $newPost
        ]);
    }

    public function postsOptions (
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado',
                401
            ]);
        }

        $id_post = $request->get('id_post');
        if (!$id_post) {
            return $this->json([
                'message' => 'No encontrado el id del post.',
                404
            ]);
        }

        $post = $this->getDoctrine()
            ->getRepository(Posts::class)
            ->findOneBy(['id' => $id_post, 'user' => $user]);
        if (!$post) {
            return $this->json([
                'message' => 'No se encontró el post con el id especificado.',
                404
            ]);
        }

        if ($request->isMethod('GET')) {
            return $this->json([
                'message' => 'Post encontrado',
                'data' => [
                    'id' => $post->getId(),
                    'user' => [
                        'id' => $post->getUser()->getId(),
                        'name' => $post->getUser()->getName()
                    ],
                    'content' => $post->getContent(),
                    'location_name' => $post->getLocationName(),
                    'visibility' => $post->getVisibility(),
                    'status' => $post->getStatus(),
                    'is_ad' => $post->getIsAd(),
                    'reaction_count' => $post->getReactionCount(),
                    'comment_count' => $post->getCommentCount(),
                    'save_count' => $post->getSaveCount(),
                    'created_at' => $post->getCreatedAt(),
                    'updated_at' => $post->getUpdatedAt()
                ]
            ]);
        }

        if ($request->isMethod('PATCH')){
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json([
                    'message' => 'JSON invalido.',
                    400
                ]);
            }

            if (isset($data['content'])) {
                $post->setContent($data['content']);
            }
            if (isset($data['visibility'])) {
                $post->setVisibility($data['visibility']);
            }
            if (isset($data['location_name'])) {
                $post->setLocationName($data['location_name']);
            }
            if (isset($data['status'])) {
                $post->setStatus($data['status']);
            }
            $post->setCreatedAt(new \DateTime('now'));
            $post->setUpdatedAt(new \DateTime('now'));

            $em->persist($post);
            $em->flush();

            return $this->json([
                'message' => 'Se ha actualizado el post con el id especificado.',
            ]);
        }

        if ($request->isMethod('DELETE')){
            $em->remove($post);
            $em->flush();

            return $this->json([
                'message' => 'Se ha eliminado el post con el id especificado.',
            ]);
        }

        return $this->json([
            'message' => 'El post creado con exito.'
        ]);
    }

    public function mediaCreate(
        Request                $request,
        SerializerInterface    $serializer,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        $post_id = $request->get('id_post');
        if (!$post_id) {
            return $this->json([
                'message' => 'No se ha encontrado el id del post.'
            ]);
        }

        $post = $em->getRepository(Posts::class)
            ->find($post_id);
        if (!$post) {
            return $this->json([
                'message' => 'No se encontró el post con el id especificado.',
            ], 404);
        }

        $newMediaPost = new PostMedia();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json([
                    'message' => 'JSON inválido.',
                ], 400);
            }

            $newMediaPost->setPost($post);
            $newMediaPost->setMediaUrl($data['media_url']);
            $newMediaPost->setMediaType($data['media_type']);
            $newMediaPost->setMimeType($data['mime_type']);
            $newMediaPost->setSortOrder($data['sort_order']);
            $newMediaPost->setWidth($data['width']);
            $newMediaPost->setHeight($data['height']);
            $newMediaPost->setDurationSeconds($data['duration_seconds'] ?? null);
            $newMediaPost->setCreatedAt(new \DateTime('now'));

            $em->persist($newMediaPost);
            $em->flush();
        }

        return $this->json([
            'message' => 'Media añadida con exito.',
            'data' => [
                'id' => $newMediaPost->getId(),
                'media_url' => $newMediaPost->getMediaUrl(),
                'media_type' => $newMediaPost->getMediaType(),
                'sort_order' => $newMediaPost->getSortOrder(),
                'width' => $newMediaPost->getWidth(),
                'height' => $newMediaPost->getHeight(),
                'duration_seconds' => $newMediaPost->getDurationSeconds(),
                'post_id' => $post_id
            ]
        ]);
    }

    public function deleteMedia(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        $post_id = $request->get('id_post');
        if (!$post_id) {
            return $this->json([
                'message' => 'No se ha encontrado el id del post.'
            ]);
        }

        $post = $em->getRepository(Posts::class)
            ->findOneBy([
                'id' => $post_id,
                'user' => $user
            ]);
        if (!$post) {
            return $this->json([
                'message' => 'No se encontró el post con el id especificado.',
            ], 404);
        }

        if ($request->isMethod('DELETE')) {
            $media_id = $request->get('id_media');
            if (!$media_id) {
                return $this->json([
                    'message' => 'No se ha encontrado el id del media con el id especificado.'
                ], 404);
            } else {
                $media = $em->getRepository(PostMedia::class)
                    ->findOneBy(['id' => $media_id, 'post' => $post]);

                if (!$media) {
                    return $this->json([
                        'message' => 'No se encontró el media con el id especificado.'
                    ], 404);
                }

                $em->remove($media);
                $em->flush();
            }

            return $this->json([
                'message' => 'Media eliminado con exito.'
            ]);
        }

        return $this->json([
            'message' => 'No se ha encontrado el id del post con el id especificado.',
        ]);
    }

    public function savePost(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $postId = $request->get('id_post');

        $post = $em->getRepository(Posts::class)
            ->find($postId);

        if (!$post) {
            return $this->json(['message' => 'Post no encontrado.'], 404);
        }

        $savedPost = $em->getRepository(SavedPosts::class)
            ->findOneBy([
            'post' => $post,
            'user' => $user
        ]);

        if ($request->isMethod('POST')) {
            if ($savedPost) {
                return $this->json(['message' => 'Este post ya está guardado.'], 409);
            }

            $savedPost = new SavedPosts();
            $savedPost->setPost($post);
            $savedPost->setUser($user);
            $savedPost->setSavedAt(new \DateTime());

            $em->persist($savedPost);
            $em->flush();

            return $this->json([
                'message' => 'Post guardado correctamente.',
                'data' => [
                    'id' => $savedPost->getId(),
                    'post_id' => $post->getId(),
                    'user_id' => $user->getId(),
                    'saved_at' => $savedPost->getSavedAt()->format('Y-m-d H:i:s')
                ]
            ], 201);
        }

        if ($request->isMethod('DELETE')) {
            if (!$savedPost) {
                return $this->json(['message' => 'Este post no estaba guardado.'], 404);
            }

            $em->remove($savedPost);
            $em->flush();

            return $this->json([
                'message' => 'Post eliminado de guardados.',
                'data' => [
                    'post_id' => $post->getId(),
                    'user_id' => $user->getId(),
                    'removed_at' => (new \DateTime())->format('Y-m-d H:i:s')
                ]
            ]);
        }

        return $this->json(['message' => 'Método no permitido.'], 405);
    }

    public function listSaved(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $savedPosts = $em->getRepository(SavedPosts::class)
            ->findBy(['user' => $user], ['savedAt' => 'DESC']);

        if (empty($savedPosts)) {
            return $this->json([
                'message' => 'No tienes posts guardados.',
                'data' => []
            ]);
        }

        if($request->isMethod('GET')){
            $savedPostsData = array_map(function ($savedPost) {
                $post = $savedPost->getPost();
                return [
                    'id' => $savedPost->getId(),
                    'saved_at' => $savedPost->getSavedAt()->format('Y-m-d H:i:s'),
                    'post' => [
                        'id' => $post->getId(),
                        'user' => [
                            'id' => $post->getUser()->getId(),
                            'name' => $post->getUser()->getName()
                        ],
                        'content' => $post->getContent(),
                        'location_name' => $post->getLocationName(),
                        'visibility' => $post->getVisibility(),
                        'status' => $post->getStatus(),
                        'is_ad' => $post->getIsAd(),
                        'reaction_count' => $post->getReactionCount(),
                        'comment_count' => $post->getCommentCount(),
                        'save_count' => $post->getSaveCount(),
                        'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                        'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s')
                    ]
                ];
            }, $savedPosts);
        }

        return $this->json([
            'message' => 'Posts guardados obtenidos correctamente.',
            'data' => $savedPostsData
        ]);
    }

    public function reactionsPost(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $postId = $request->get('id_post');

        $post = $em->getRepository(Posts::class)
            ->find($postId);

        if (!$post) {
            return $this->json(['message' => 'Post no encontrado.'], 404);
        }

        $reaction = $em->getRepository(PostReactions::class)
            ->findOneBy([
                'post' => $post,
                'user' => $user
            ]);

        if ($request->isMethod('GET')) {
            if (!$reaction) {
                return $this->json([
                    'message' => 'No hay reacción para este post.',
                    'data' => null
                ]);
            }

            $reactions = $em->getRepository(PostReactions::class)
                ->findBy(['post' => $post]);

            if (empty($reactions)) {
                return $this->json([
                    'message' => 'No hay reacciones para este post.',
                    'data' => []
                ]);
            }

            $reactionsData = array_map(function ($reaction) {
                return [
                    'id' => $reaction->getId(),
                    'type' => $reaction->getType(),
                    'user' => [
                        'id' => $reaction->getUser()->getId(),
                        'name' => $reaction->getUser()->getName()
                    ],
                    'created_at' => $reaction->getCreatedAt()
                ];
            }, $reactions);

            return $this->json([
                'message' => 'Reacciones encontradas.',
                'data' => $reactionsData
            ]);
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (!$data || empty($data['type'])) {
                return $this->json(['message' => 'Debes enviar el tipo de reacción.'], 400);
            }

            if ($reaction) {
                $reaction->setType($data['type']);

                $em->flush();

                return $this->json([
                    'message' => 'Reacción actualizada correctamente.'
                ]);
            }

            $newReaction = new PostReactions();
            $newReaction->setPost($post);
            $newReaction->setUser($user);
            $newReaction->setType($data['type']);
            $newReaction->setCreatedAt(new \DateTime());

            $em->persist($newReaction);
            $em->flush();

            return $this->json([
                'message' => 'Reacción añadida correctamente.'
            ], 201);
        }

        if ($request->isMethod('DELETE')) {
            if (!$reaction) {
                return $this->json(['message' => 'Este post no tenía una reacción.'], 404);
            }

            $em->remove($reaction);
            $em->flush();

            return $this->json([
                'message' => 'Reacción eliminada del post.'
            ]);
        }

        return $this->json(['message' => 'Método no permitido.'], 405);
    }

    public function commentsPost(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $postId = $request->get('id_post');

        $post = $em->getRepository(Posts::class)
            ->find($postId);

        if (!$post) {
            return $this->json(['message' => 'Comment no encontrado.'], 404);
        }

        if ($request->isMethod('GET')) {
            $comments = $em->getRepository(Comments::class)
                ->findBy(['post' => $post]);

            if (!$comments) {
                return $this->json([
                    'message' => 'No se encontraron comentarios de este post.'
                ]);
            }

            $commentsData = array_map(function ($comment) use ($postId, $post) {
                return [
                    'id' => $comment->getId(),
                    'user' => [
                        'id' => $comment->getUser()->getId(),
                        'name' => $comment->getUser()->getName(),
                        'avatar' => $comment->getUser()->getProfileImageUrl()
                    ],
                    'post' => [
                        'id' => $postId,
                        'created_at' => $post->getCreatedAt()
                    ],
                    'content' => $comment->getContent(),
                    'reactions' => $comment->getReactionCount(),
                    'replys' => $comment->getReplyCount(),
                    'created_at' => $comment->getCreatedAt(),
                ];
            }, $comments);

            return $this->json([
                'message'=>'Comentarios del post.',
                'data' => $commentsData
            ]);
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json([
                    'message' => 'JSON inválido.',
                ], 400);
            }

            $newComment = new Comments();
            $newComment->setPost($post);
            $newComment->setUser($user);
            $newComment->setContent($data['content']);
            $newComment->setReactionCount(0);
            $newComment->setReplyCount(0);
            $newComment->setCreatedAt(new \DateTime('now'));
            $newComment->setUpdatedAt(new \DateTime('now'));

            $em->persist($newComment);
            $em->flush();

            $commentsData = array_map(function ($comment) {
                return [
                    'id' => $comment->getId(),
                    'user' => [
                        'id' => $comment->getUser()->getId(),
                        'name' => $comment->getUser()->getName(),
                        'avatar' => $comment->getUser()->getProfileImageUrl(),],
                    'post' => [
                        'id' => $comment->getPost()->getId(),
                        'content' => $comment->getPost()->getContent(),
                        'created_at' => $comment->getPost()->getCreatedAt()->format('Y-m-d H:i:s'),
                        'updated_at' => $comment->getPost()->getUpdatedAt()->format('Y-m-d H:i:s')
                    ],
                    'content' => $comment->getContent(),
                    'reactions' => $comment->getReactionCount(),
                    'replys' => $comment->getReplyCount(),
                    'created_at' => $comment->getCreatedAt(),
                ];
            }, [$newComment]);

            return $this->json([
                'message'=>'Comentarios del post.',
                'data' => $commentsData
            ]);
        }

    
        return $this->json(['message' => 'Método no permitido.'], 405);
    
    }

}
