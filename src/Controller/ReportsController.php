<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommentReports;
use App\Entity\Comments;
use App\Entity\PostReports;
use App\Entity\Posts;
use App\Entity\UserReports;
use App\Entity\Users;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReportsController extends AbstractController
{

    public function reportsPosts(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $id_posts = $request->get('id_post');

        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        if ($request->isMethod('POST')) {
            if (!$id_posts) {
                return $this->json([
                    'message' => 'ID del post es requerido.'
                ], 400);
            }

            $post = $em->getRepository(Posts::class)->find($id_posts);
            if (!$post) {
                return $this->json([
                    'message' => 'Post no encontrado.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $reason = $data['reason'] ?? null;

            $report = new PostReports();
            $report->setReporter($user);
            $report->setPost($post);
            $report->setReason($reason);
            $report->setCreatedAt(new DateTime());

            $em->persist($report);
            $em->flush();

            return $this->json([
                'message' => 'Post reportado exitosamente.'
            ], 200);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los posts.'
        ]);
    }

    public function reportsComments(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $id_comments = $request->get('id_comment');

        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        if ($request->isMethod('POST')) {
            if (!$id_comments) {
                return $this->json([
                    'message' => 'ID del comentario es requerido.'
                ], 400);
            }

            $comment = $em->getRepository(Comments::class)->find($id_comments);
            if (!$comment) {
                return $this->json([
                    'message' => 'Comentario no encontrado.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $reason = $data['reason'] ?? null;

            $report = new CommentReports();
            $report->setReporter($user);
            $report->setComment($comment);
            $report->setReason($reason);
            $report->setCreatedAt(new DateTime());

            $em->persist($report);
            $em->flush();

            return $this->json([
                'message' => 'Comentario reportado exitosamente.'
            ], 200);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los comentarios.'
        ]);
    }

    public function reportsUsers(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $id_users = $request->get('id_user');

        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'No autenticado.',
                401
            ]);
        }

        if ($request->isMethod('POST')) {
            if (!$id_users) {
                return $this->json([
                    'message' => 'ID del usuario es requerido.'
                ], 400);
            }

            $reportedUser = $em->getRepository(Users::class)->find($id_users);
            if (!$reportedUser) {
                return $this->json([
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $reason = $data['reason'] ?? null;

            $report = new UserReports();
            $report->setReporter($user);
            $report->setReportedUser($reportedUser);
            $report->setReason($reason);
            $report->setCreatedAt(new DateTime());

            $em->persist($report);
            $em->flush();


            return $this->json([
                'message' => 'Usuario reportado exitosamente.'
            ], 200);
        }

        return $this->json([
            'message' => 'No se ha encontrado el registro de los usuarios.'
        ]);
    }
}
