<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\Tags;
use App\Entity\UserTopic;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class TagsController extends AbstractController
{
    public function tagsList(
        Request                $request,
        SerializerInterface    $serializer,
        EntityManagerInterface $em
    ): JsonResponse
    {
        if ($request->isMethod('GET')) {
            $tags = $em->getRepository(Tags::class)
                ->findAll();

            if (!$tags) {
                return $this->json([
                    'message' => 'No hay tags disponibles.'
                ]);
            }

            $tagsList = [];
            foreach ($tags as $tag) {
                $tagsList[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'created_at' => $tag->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }

            return $this->json([
                'Count' => count($tags),
                'List tags' => $tagsList
            ]);
        }
        return $this->json([
            'message' => 'No hay tags.',
        ]);
    }

    public function slugTags(
        Request                $request,
        SerializerInterface    $serializer,
        EntityManagerInterface $em
    ): JsonResponse {
        $id_slug = $request->get('slug');
        if (!$id_slug){
            return $this->json([
                'message' => 'No se ha encontrado el slug del tag.',
                400
            ]);
        }

        $tag = $em->getRepository(Tags::class)
            ->findOneBy(['id' => $id_slug]);

        if (!$tag) {
            return $this->json([
                'message' => 'No hay tag disponibles.'
            ]);
        }

        return $this->json([
            'id' => $tag->getId(),
            'slug' => $tag->getSlug(),
            'created_at' => $tag->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function tagPosts(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_post = $request->get('id_post');
        if (!$id_post) {
            return $this->json([
                'message' => 'No se ha encontrado el id del tag.',
                400
            ]);
        }

        if ($request->isMethod('GET')) {
            $posts = $em->getRepository(Posts::class)
                ->findOneBy(['id' => $id_post]);

            if (!$posts) {
                return $this->json([
                    'message' => 'No se ha encontrado el post.'
                ], 404);
            }

            $tags = $posts->getTag();

            if (!$tags || count($tags) === 0) {
                return $this->json([
                    'message' => 'No hay tags asociados a este post.'
                ], 404);
            }

            $tagsList = [];
            foreach ($tags as $tag) {
                $tagsList[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'created_at' => $tag->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }

            return $this->json([
                'post' => [
                    'id' => $posts->getId(),
                    'content' => $posts->getContent()
                ],
                'count' => count($tagsList),
                'tags' => $tagsList
            ]);
        }

        

        return $this->json([
            'message' => 'No hay tags disponibles.'
        ]);
    }

    public function tagActionsPosts(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_post = $request->get('id_post');
        if (!$id_post) {
            return $this->json([
                'message' => 'No se ha encontrado el id del post.'
            ], 400);
        }

        $id_tag = $request->get('id_tag');
        if (!$id_tag) {
            return $this->json([
                'message' => 'No se ha encontrado el id del tag.'
            ], 400);
        }

        if ($request->isMethod('POST')) {
            $id_tag = $request->get('id_tag');

            $tag = $em->getRepository(Tags::class)
                ->find($id_tag);
            if (!$tag) {
                return $this->json([
                    'message' => 'Tag no encontrado.'
                ], 404);
            }

            $post = $em->getRepository(Posts::class)
                ->find($id_post);
            if (!$post) {
                return $this->json([
                    'message' => 'Post no encontrado.'
                ], 404);
            }

            if ($post->getTag()->contains($tag)) {
                return $this->json([
                    'message' => 'El tag ya está asociado al post.'
                ], 400);
            }

            $post->addTag($tag);
            $em->flush();

            return $this->json([
                'message' => 'Tag añadido al post correctamente.'
            ]);
        }

        if ($request->isMethod('DELETE')) {
            $id_tag = $request->get('id_tag');

            $post = $em->getRepository(Posts::class)->find($id_post);
            if (!$post) {
                return $this->json([
                    'message' => 'Post no encontrado.'
                ], 404);
            }

            // Si id_tag es 'all' o 'todos', eliminar todos los tags
            if ($id_tag === 'all' || $id_tag === 'todos') {
                $tagsCount = count($post->getTag());

                if ($tagsCount === 0) {
                    return $this->json([
                        'message' => 'El post no tiene tags asociados.'
                    ], 404);
                }

                foreach ($post->getTag() as $tag) {
                    $post->removeTag($tag);
                }

                $em->flush();

                return $this->json([
                    'message' => "Todos los tags ($tagsCount) han sido eliminados del post."
                ]);
            }

            // Eliminar un tag específico
            $tag = $em->getRepository(Tags::class)->find($id_tag);
            if (!$tag) {
                return $this->json([
                    'message' => 'Tag no encontrado.'
                ], 404);
            }

            if (!$post->getTag()->contains($tag)) {
                return $this->json([
                    'message' => 'El tag no está asociado al post.'
                ], 404);
            }

            $post->removeTag($tag);
            $em->flush();

            return $this->json([
                'message' => 'Tag eliminado del post correctamente.'
            ]);
        }

        return $this->json([
            'message' => 'Método no permitido.'
        ], 405);
    }

    public function followTags(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_tag = $request->get('id_tag');
        if (!$id_tag) {
            return $this->json([
                'message' => 'No se ha encontrado el id del tag.'
            ], 400);
        }

        $tag = $em->getRepository(Tags::class)
            ->find($id_tag);
        if (!$tag) {
            return $this->json([
                'message' => 'Tag no encontrado.'
            ], 404);
        }

        if ($request->isMethod('POST')) {
            $existingUserTopic = $em->getRepository(UserTopic::class)
                ->findOneBy(['user' => $user, 'tag' => $tag]);

            if ($existingUserTopic) {
                return $this->json([
                    'message' => 'Ya estás siguiendo este tag.'
                ], 400);
            }

            $userTopic = new UserTopic();
            $userTopic->setUser($user);
            $userTopic->setTag($tag);
            $userTopic->setCreatedAt(new \DateTime());

            $em->persist($userTopic);
            $em->flush();

            return $this->json([
                'message' => 'Tag seguido correctamente.',
                'tag' => [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                    'isFollowing' => true,
                ]
            ]);
        }

        if ($request->isMethod('DELETE')) {
            $userTopic = $em->getRepository(UserTopic::class)
                ->findOneBy(['user' => $user, 'tag' => $tag]);

            if (!$userTopic) {
                return $this->json([
                    'message' => 'No estás siguiendo este tag.',
                    'isFollowing' => false
                ], 404);
            }

            $em->remove($userTopic);
            $em->flush();

            return $this->json([
                'message' => 'Has dejado de seguir el tag: ' . $tag->getName()
            ]);
        }
    
        return $this->json([
            'message' => 'Método no permitido.'
        ], 405);
    }
}
