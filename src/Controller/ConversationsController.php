<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ConversationParticipants;
use App\Entity\Conversations;
use App\Entity\MessageReads;
use App\Entity\Messages;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ConversationsController extends AbstractController
{
    public function conversations(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $conversationsListUser = $em->getRepository(ConversationParticipants::class)
            ->findBy(['user' => $user, 'leftAt' => null]);

        if (!$conversationsListUser) {
            return $this->json([
                'message' => 'No hay registros de conversaciones.'
            ], 404);
        }

        if ($request->isMethod('GET')) {
            $conversations = [];
            foreach ($conversationsListUser as $participant) {
                $conversation = $participant->getConversation();

                $otherParticipants = $em->getRepository(ConversationParticipants::class)
                    ->createQueryBuilder('cp')
                    ->where('cp.conversation = :conversation')
                    ->andWhere('cp.user != :currentUser')
                    ->andWhere('cp.leftAt IS NULL')
                    ->setParameter('conversation', $conversation)
                    ->setParameter('currentUser', $user)
                    ->getQuery()
                    ->getResult();

                $conversations[] = [
                    'conversation' => [
                        'id' => $conversation->getId(),
                        'title' => $conversation->getTitle(),
                    ],
                    'otherParticipants' => array_map(function ($participant) {
                        return [
                            'id' => $participant->getId(),
                            'joinedAt' => $participant->getJoinedAt()->format('Y-m-d H:i:s'),
                            'user' => [
                                'id' => $participant->getUser()->getId(),
                                'name' => $participant->getUser()->getName(),
                            ]
                        ];
                    }, $otherParticipants)
                ];
            }

            return $this->json([
                'conversations' => $conversations
            ]);
        }
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['other_user_id']) || empty($data['other_user_id'])) {
                return $this->json(['message' => 'El ID del otro usuario es requerido.'], 400);
            }

            $otherUser = $em->getRepository(Users::class)
                ->find($data['other_user_id']);
            if (!$otherUser) {
                return $this->json(['message' => 'Usuario no encontrado.'], 404);
            }

            if ($otherUser->getId() === $user->getId()) {
                return $this->json(['message' => 'No puedes crear una conversación contigo mismo.'], 400);
            }

            $conversation = new Conversations();
            if (method_exists($conversation, 'setCreatedAt')) {
                $conversation->setCreatedAt(new \DateTime('now'));
                $conversation->setUpdatedAt(new \DateTime('now'));
            }
            $em->persist($conversation);
            $em->flush();

            $conversationParticipant = new ConversationParticipants();
            $conversationParticipant->setConversation($conversation);
            $conversationParticipant->setUser($user);
            $conversationParticipant->setJoinedAt(new \DateTime('now'));
            $em->persist($conversationParticipant);

            $otherParticipant = new ConversationParticipants();
            $otherParticipant->setConversation($conversation);
            $otherParticipant->setUser($otherUser);
            $otherParticipant->setJoinedAt(new \DateTime('now'));
            $em->persist($otherParticipant);
        
            $em->flush();
        
            return $this->json([
                'message' => 'Conversación creada exitosamente.',
                'conversation' => [
                    'id' => $conversation->getId(),
                ]
            ], 201);
        }

        return $this->json([
            'message' => 'Método no permitido.'],
            405);
    }

    public function conversationsSingle(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado.'], 401);
        }

        $id_conversation = $request->get('id_conversation');
        if (!$id_conversation) {
            return $this->json([
                'message' => 'ID de conversación no proporcionado.'
            ], 400);
        }

        $conversation = $em->getRepository(Conversations::class)
            ->find($id_conversation);
        if (!$conversation) {
            return $this->json(['message' => 'Conversación no encontrada.'], 404);
        }

        $participant = $em->getRepository(ConversationParticipants::class)
            ->findOneBy([
                'conversation' => $conversation,
                'user' => $user,
                'leftAt' => null
            ]);

        if (!$participant) {
            return $this->json([
                'message' => 'No tienes acceso a esta conversación.'
            ], 403);
        }

        if ($request->isMethod('GET')) {
            $otherParticipants = $em->getRepository(ConversationParticipants::class)
                ->createQueryBuilder('cp')
                ->where('cp.conversation = :conversation')
                ->andWhere('cp.user != :currentUser')
                ->andWhere('cp.leftAt IS NULL')
                ->setParameter('conversation', $conversation)
                ->setParameter('currentUser', $user)
                ->getQuery()
                ->getResult();

            return $this->json([
                'conversation' => [
                    'id' => $conversation->getId(),
                    'title'=>$conversation->getTitle(),
                    'createdAt' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $conversation->getUpdatedAt()->format('Y-m-d H:i:s'),
                ],
                'participants' => array_map(function ($participant) {
                    return [
                        'id' => $participant->getId(),
                        'joinedAt' => $participant->getJoinedAt()->format('Y-m-d H:i:s'),
                        'isAdmin' => $participant->getIsAdmin(),
                        'user' => [
                            'id' => $participant->getUser()->getId(),
                            'name' => $participant->getUser()->getName(),
                        ]
                    ];
                }, $otherParticipants)
            ]);
        }
    
        return $this->json([
            'message' => 'Método no permitido.'],
            405);
    }

    public function messages(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_conversation = $request->get('id_conversation');
        if (!$id_conversation) {
            return $this->json(['message' => 'ID de conversación no proporcionado.'], 400);
        }

        $conversation = $em->getRepository(Conversations::class)
            ->find($id_conversation);
        if (!$conversation) {
            return $this->json(['message' => 'Conversación no encontrada.'], 404);
        }

        $participant = $em->getRepository(ConversationParticipants::class)
            ->findOneBy([
                'conversation' => $conversation,
                'user' => $user,
                'leftAt' => null
            ]);
        if (!$participant) {
            return $this->json(['message' => 'No tienes acceso a esta conversación.'], 403);
        }

        if ($request->isMethod('GET')) {
            $messages = $em->getRepository(Messages::class)
                ->createQueryBuilder('m')
                ->where('m.conversation = :conversation')
                ->setParameter('conversation', $conversation)
                ->orderBy('m.createdAt', 'ASC')
                ->getQuery()
                ->getResult();

            $messagesData = array_map(function ($message) {
                return [
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                    'sender' => [
                        'id' => $message->getSender()->getId(),
                        'name' => $message->getSender()->getName(),
                    ]
                ];
            }, $messages);

            return $this->json(['messages' => $messagesData]);
        }

        if ($request->isMethod('POST')) {

            if (!isset($data['content']) || empty(trim($data['content']))) {
                return $this->json(['message' => 'El contenido del mensaje es requerido.'], 400);
            }

            $message = new Messages();
            $message->setConversation($conversation);
            $message->setSender($user);
            $message->setContent($data['content']);
            $message->setCreatedAt(new \DateTime('now'));
            $message->setUpdatedAt(new \DateTime('now'));

            $em->persist($message);
            $em->flush();

            return $this->json([
                'message' => 'Mensaje enviado exitosamente.',
                'data' => [
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ], 201);
        }

        return $this->json(['message' => 'Método no permitido.'], 405);
    }

    public function messagesAction(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_conversation = $request->get('id_conversation');
        if (!$id_conversation) {
            return $this->json(['message' => 'ID de conversación no proporcionado.'], 400);
        }

        $conversation = $em->getRepository(Conversations::class)
            ->find($id_conversation);
        if (!$conversation) {
            return $this->json(['message' => 'Conversación no encontrada.'], 404);
        }

        $id_message = $request->get('id_message');

        $message = $em->getRepository(Messages::class)->findOneBy([
            'id' => $id_message,
            'conversation' => $conversation,
            'sender' => $user

        ]);
        if(!$message){
            return $this->json([
                'message'=>'Message no encontrado.'
            ]);
        }

        if($request->isMethod('PATCH')){
            if(isset($data['content'])){
                $message->setContent($data['content']);
            }

            $message->setUpdatedAt(new \DateTime('now'));

            $em->persist($message);
            $em->flush();

            return $this->json(['message' => 'Mensaje editado exitosamente.'], 200);
        }

        if ($request->isMethod('DELETE')){
            $em->remove($message);
            $em->flush();

            return $this->json(['message' => 'Mensaje eliminado exitosamente.'], 200);
        }

        return $this->json(['message' => 'Método no permitido.'], 405);
    }

    public function messagesRead(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'No autenticado'], 401);
        }

        $id_conversation = $request->get('id_conversation');
        if (!$id_conversation) {
            return $this->json(['message' => 'ID de conversación no proporcionado.'], 400);
        }

        $conversation = $em->getRepository(Conversations::class)->find($id_conversation);
        if (!$conversation) {
            return $this->json(['message' => 'Conversación no encontrada.'], 404);
        }

        $id_message = $request->get('id_message');
        if (!$id_message) {
            return $this->json(['message' => 'ID de mensaje no proporcionado.'], 400);
        }

        $message = $em->getRepository(Messages::class)->findOneBy([
            'id' => $id_message,
            'conversation' => $conversation
        ]);
        if (!$message) {
            return $this->json(['message' => 'Mensaje no encontrado.'], 404);
        }

        $participant = $em->getRepository(ConversationParticipants::class)->findOneBy([
            'conversation' => $conversation,
            'user' => $user
        ]);
        if (!$participant) {
            return $this->json([
                'message' => 'No perteneces a esta conversación.'
            ], 403);
        }

        if ($message->getSender()->getId() === $user->getId()) {
            return $this->json([
                'message' => 'No puedes marcar como leído tu propio mensaje.'
            ], 400);
        }
        $alreadyRead = $em->getRepository(MessageReads::class)->findOneBy([
            'message' => $message,
            'user' => $user
        ]);

        if ($request->isMethod('POST')) {
            if ($alreadyRead) {
                return $this->json([
                    'message' => 'El mensaje ya estaba marcado como leído.'
                ], 200);
            }

            $messageRead = new MessageReads();
            $messageRead->setMessage($message);
            $messageRead->setUser($user);
            $messageRead->setReadAt(new \DateTime());

            $em->persist($messageRead);

            if (method_exists($participant, 'setLastReadMessage')) {
                $participant->setLastReadMessage($message);
            }

            $em->flush();

            return $this->json([
                'message' => 'Mensaje marcado como leído.',
                'data' => [
                    'conversation_id' => $conversation->getId(),
                    'message_id' => $message->getId(),
                    'user_id' => $user->getId(),
                    'read_at' => $messageRead->getReadAt()->format('Y-m-d H:i:s')
                ]
            ], 201);
        }

        return $this->json(['message' => 'Método no permitido.'], 405);
    }

}
