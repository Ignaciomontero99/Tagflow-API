<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommentReactions;
use App\Entity\Comments;
use App\Entity\Posts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CommentsController extends AbstractController
{
    public function commentsEdit(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user){
            return $this->json([
                'message'=>'No autenticado.',
                401
            ]);
        }

        $idComment = $request->get('id_comment');
        if (!$idComment){
            return $this->json([
                'message' => ['No se ha recibido el $idComment'],
                404
            ]);
        }

        $comment = $em->getRepository(Comments::class)
            ->findOneBy([
                'id' => $idComment,
                'user' => $user
            ]);
        if(!$comment){
            return $this->json([
                'message' => 'No se ha encontrado el registro de los comentarios con el id especificado: '
            ]);
        }

        if ($request->isMethod('PATCH')){
            $data = json_decode($request->getContent(), true);
            if(!$data) {
                return $this->json(['message' => 'Invalid JSON'], 404);
            }

            if (isset($data['content'])){
                $comment->setContent($data['content']);
            }

            $comment->setUpdatedAt(new \DateTime('now'));

            $em->persist($comment);
            $em->flush();

            return $this->json([
                'message' => 'Se ha actualizado el comentario con el id: ' . $idComment,
            ]);
        }

        if ($request->getMethod() == 'DELETE') {
            if(!$comment){
                return $this->json([
                   'message' => 'Este post no tiene comentarios del usuario.'
                ]);
            }
            $em->remove($comment);
            $em->flush();

            return $this->json([
                'message' => 'Se ha eliminado el comentario con el id: ' . $idComment,
                200
            ]);
        }
        return $this ->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }

    public function commentsReplies(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $id_comment = $request->get('id_comment');
        if (!$id_comment) {
            return $this->json([
                'message' => 'No se ha encontrado el id del comentario.',
                404
            ]);
        }

        $comment = $em->getRepository(Comments::class)
            ->findOneBy([
                'id' => $id_comment
            ]);
        if (!$comment) {
            return $this->json(['message' => 'No se ha encontrado el registro de los comentarios con el id especificado: ' . $id_comment]);
        }

        if ($request->isMethod('GET')) {
            $replies = $em->getRepository(Comments::class)
                ->findBy([
                    'parentComment' => $comment
                ], ['createdAt' => 'DESC']);

            $repliesData = [];
            foreach ($replies as $reply) {
                $userReaction = $em->getRepository(CommentReactions::class)
                    ->findOneBy([
                        'comment' => $reply,
                        'user' => $user
                    ]);

                $repliesData[] = [
                    'id' => $reply->getId(),
                    'content' => $reply->getContent(),
                    'reaction_count' => $reply->getReactionCount(),
                    'reply_count' => $reply->getReplyCount(),
                    'created_at' => $reply->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $reply->getUser()->getId(),
                        'name' => $reply->getUser()->getName(),
                        'avatar' => $reply->getUser()->getProfileImageUrl()
                    ],
                    'user_reaction' => $userReaction ? [
                        'id' => $userReaction->getId(),
                        'type' => $userReaction->getType()
                    ] : null
                ];
            }

            return $this->json([
                'message' => 'Listado de respuestas.',
                'data' => [
                    'parent_comment' => [
                        'id' => $comment->getId(),
                        'content' => $comment->getContent(),
                        'reaction_count' => $comment->getReactionCount(),
                        'reply_count' => $comment->getReplyCount(),
                        'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                        'user' => [
                            'id' => $comment->getUser()->getId(),
                            'name' => $comment->getUser()->getName(),
                            'avatar' => $comment->getUser()->getProfileImageUrl()
                        ]
                    ],
                    'replies' => $repliesData,
                    'total' => count($repliesData)
                ]
            ]);
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json(['message' => 'Invalid JSON'], 404);
            }

            $newRepliesComment = new Comments();
            $newRepliesComment->setUser($user);
            $newRepliesComment->setPost($comment->getPost());
            $newRepliesComment->setContent($data['content']);
            $newRepliesComment->setParentComment($comment);
            $newRepliesComment->setReactionCount(0);
            $newRepliesComment->setReplyCount(0);
            $newRepliesComment->setCreatedAt(new \DateTime('now'));
            $newRepliesComment->setUpdatedAt(new \DateTime('now'));

            $em->persist($newRepliesComment);
            $em->flush();

            $commentsData = [
                'parent_comment' => [
                    'id' => $comment->getId(),
                    'content' => $comment->getContent(),
                    'reaction_count' => $comment->getReactionCount(),
                    'reply_count' => $comment->getReplyCount(),
                    'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $comment->getUser()->getId(),
                        'name' => $comment->getUser()->getName(),
                        'avatar' => $comment->getUser()->getProfileImageUrl()
                    ]
                ],
                'new_reply' => [
                    'id' => $newRepliesComment->getId(),
                    'content' => $newRepliesComment->getContent(),
                    'reaction_count' => $newRepliesComment->getReactionCount(),
                    'reply_count' => $newRepliesComment->getReplyCount(),
                    'created_at' => $newRepliesComment->getCreatedAt()->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $newRepliesComment->getUser()->getId(),
                        'name' => $newRepliesComment->getUser()->getName(),
                        'avatar' => $newRepliesComment->getUser()->getProfileImageUrl(),
                    ]
                ]
            ];
    
            return $this->json([
                'message' => 'Nueva respuesta al comentario.',
                'data' => $commentsData
            ]);
        }
    
        return $this ->json([
            'message' => 'Método no permitido.'
        ], 405);
    }

    public function commentsReactions(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $commentId = $request->get('id_comment');

        $comments = $em->getRepository(Comments::class)
            ->find($commentId);

        if (!$comments) {
            return $this->json(['message' => 'Comment no encontrado.'], 404);
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $comment = $em->getRepository(Comments::class)
                ->findOneBy(['id' => $comments]);
            if (!$comment) {
                return $this->json([
                    'message' => 'No se ha encontrado el comentario con el id: ' . $comments,
                ], 404);
            }

            $existingReaction = $em->getRepository(CommentReactions::class)
                ->findOneBy([
                    'comment' => $comment,
                    'user' => $user
                ]);

            if ($existingReaction) {
                if ($existingReaction->getType() !== $data['type']) {
                    $existingReaction->setType($data['type']);
                    $em->flush();

                    return $this->json([
                        'message' => 'Reacción al comentario actualizada con éxito.',
                        'data' => [
                            'reaction' => [
                                'id' => $existingReaction->getId(),
                                'type' => $existingReaction->getType(),
                                'created_at' => $existingReaction->getCreatedAt()->format('Y-m-d H:i:s'),
                                'user' => [
                                    'id' => $user->getId(),
                                    'name' => $user->getName(),
                                    'avatar' => $user->getProfileImageUrl()
                                ],
                                'comment' => [
                                    'id' => $comment->getId(),
                                    'content' => $comment->getContent()
                                ]
                            ]
                        ]
                    ]);
                }

                return $this->json([
                    'message' => 'Ya existe una reacción del mismo tipo.'
                ], 409);
            }

            $newReactionComment = new CommentReactions();
            $newReactionComment->setUser($user);
            $newReactionComment->setComment($comment);
            $newReactionComment->setType($data['type']);
            $newReactionComment->setCreatedAt(new \DateTime('now'));

            $em->persist($newReactionComment);
            $em->flush();

            return $this->json([
                'message' => 'Reacción al comentario creada con éxito.',
                'data' => [
                    'reaction' => [
                        'id' => $newReactionComment->getId(),
                        'type' => $newReactionComment->getType(),
                        'created_at' => $newReactionComment->getCreatedAt()->format('Y-m-d H:i:s'),
                        'user' => [
                            'id' => $user->getId(),
                            'name' => $user->getName(),
                            'avatar' => $user->getProfileImageUrl()
                        ],
                        'comment' => [
                            'id' => $comment->getId(),
                            'content' => $comment->getContent()
                        ]
                    ]
                ]
            ]);
        }
        if ($request->isMethod('DELETE')){
            $reactionComment = $em->getRepository(CommentReactions::class)
                ->findOneBy([
                    'comment' => $commentId,
                    'user' => $user
                ]);
            if (!$reactionComment) {
                return $this->json([
                    'message' => 'No se ha encontrado el comentario con el id: ' . $commentId . '.',
                    404
                ]);
            }

            $em->remove($reactionComment);
            $em->flush();

            return $this->json([
                'message' => 'Reacción eliminada del comentario.'
            ]);
        }

        return $this ->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }
}
