<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommentReports;
use App\Entity\PostReports;
use App\Entity\UserReports;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ModerationController extends AbstractController
{

    public function moderationList(
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

        $roles = $user->getRoles();
        if (!in_array('ROLE_MODERATOR', $roles) && !in_array('ROLE_ADMIN', $roles)) {
            return $this->json([
                'message' => 'Acceso denegado. Solo moderadores y administradores pueden acceder.',
            ], 403);
        }

        if ($request->isMethod('GET')) {
            $allUsers = $em->getRepository(Users::class)
                ->findAll();

            $usersWithRoles = array_filter($allUsers, function ($u) {
                $userRoles = $u->getRoles();
                return in_array('ROLE_MODERATOR', $userRoles) || in_array('ROLE_ADMIN', $userRoles);
            });

            return $this->json([
                'users' => array_values($usersWithRoles)
            ]);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }

    public function moderationChangeReport(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        $type = $request->get('type');
        if(!$type){
            return $this->json([
                'message' => 'Tipo de reporte no encontrado.'
            ]);
        }

        $id_report = $request->get('id_report');
        if(!$id_report){
            return $this->json([
                'message' => 'No se ha encontrado el id del reporte.'
            ]);
        }

        $roles = $user->getRoles();
        $hasModeratorOrAdmin = in_array('ROLE_MODERATOR', $roles) || in_array('ROLE_ADMIN', $roles);

        if (!$hasModeratorOrAdmin) {
            return $this->json([
                'message' => 'Acceso denegado. Solo moderadores y administradores pueden cambiar el estado de los reportes.',
            ], 403);
        }

        $validTypes = ['posts', 'comments', 'users'];
        if (!in_array($type, $validTypes)) {
            return $this->json([
                'message' => 'Tipo de reporte inválido. Debe ser posts, comments o users.',
            ], 400);
        }

       if($request->isMethod('PATCH')) {
           $report = null;
           if ($type === 'posts') {
               $report = $em->getRepository(PostReports::class)->find($id_report);
           } elseif ($type === 'comments') {
               $report = $em->getRepository(CommentReports::class)->find($id_report);
           } elseif ($type === 'users') {
               $report = $em->getRepository(UserReports::class)->find($id_report);
           }

           if (!$report) {
               return $this->json([
                   'message' => 'Reporte no encontrado.',
               ], 404);
           }

           $data = json_decode($request->getContent(), true);
           if (!isset($data['status'])) {
               return $this->json([
                   'message' => 'El campo status es requerido.',
               ], 400);
           }

           $report->setStatus($data['status']);
           $em->flush();
       }

        return $this->json([
            'message' => 'Estado del reporte actualizado correctamente.'
        ]);
    }

    public function adminUserStatus(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        $roles = $user->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles);

        if (!$isAdmin) {
            return $this->json([
                'message' => 'Acceso denegado. Solo administradores pueden cambiar el estado de usuarios.',
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $id_user = $request->get('id_user');

        if (!isset($id_user)) {
            return $this->json([
                'message' => 'El campo user_id es requerido.',
            ], 400);
        }

        $account_status = $data['account_status'];
        if (!isset($account_status)) {
            return $this->json([
                'message' => 'El campo account_status es requerido.',
            ], 400);
        }

        $targetUser = $em->getRepository(Users::class)->find($id_user);

        if (!$targetUser) {
            return $this->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $targetUser->setAccountStatus($account_status);
        $em->flush();

        return $this->json([
            'message' => 'Estado de la cuenta actualizado correctamente.',
            'user_id' => $targetUser->getId(),
            'account_status' => $targetUser->getAccountStatus()
        ]);
    }
}
