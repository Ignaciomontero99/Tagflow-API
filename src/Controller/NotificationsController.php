<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notifications;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationsController extends AbstractController
{
    public function notifications(
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

        $notificationRepository = $em->getRepository(Notifications::class);
        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        $notificationsData = [];
        if($request->isMethod('GET')){
            foreach ($notifications as $notification) {
                $notificationsData[] = [
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'message' => $notification->getMessage(),
                    'read' => $notification->getIsRead(),
                    'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }

            return $this->json([
                'notifications' => $notificationsData
            ]);
        }

        return $this ->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }

    public function notificationsRead(
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

        if ($request->isMethod('PATCH')) {
            $idNotification = $request->get('id_notification');

            $notificationRepository = $em->getRepository(Notifications::class);
            $notification = $notificationRepository->findOneBy([
                'id' => $idNotification,
                'user' => $user
            ]);

            if (!$notification) {
                return $this->json([
                    'message' => 'Notificación no encontrada.'
                ], 404);
            }

            $notification->setIsRead(true);
            $notification->setReadAt(new \DateTime('now'));
            $em->flush();

            return $this->json([
                'message' => 'Notificación marcada como leída.',
                'notification' => [
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'message' => $notification->getMessage(),
                    'read' => $notification->getIsRead(),
                    'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ]);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }

    public function notificationsReadAll(
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

        if ($request->isMethod('PATCH')) {
            $notificationRepository = $em->getRepository(Notifications::class);
            $notifications = $notificationRepository->findBy([
                'user' => $user,
                'isRead' => false
            ]);

            foreach ($notifications as $notification) {
                $notification->setIsRead(true);
                $notification->setReadAt(new \DateTime('now'));
            }

            $em->flush();

            return $this->json([
                'message' => 'Todas las notificaciones han sido marcadas como leídas.',
                'count' => count($notifications)
            ]);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }
}
