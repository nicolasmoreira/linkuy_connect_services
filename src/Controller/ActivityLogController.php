<?php

namespace App\Controller;

use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityLogController extends AbstractController
{
    #[Route('/api/activity-log', name: 'api_activity_log_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $activityLogs = $em->getRepository(ActivityLog::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->json(array_map(static fn ($log) => [
            'id' => $log->getId(),
            'type' => $log->getActivityType()->value,
            'data' => $log->getData(),
            'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s')
        ], $activityLogs));
    }

    #[Route('/api/activity-log/latest', name: 'api_activity_log_latest', methods: ['GET'])]
    public function latest(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $latestLog = $em->getRepository(ActivityLog::class)->findOneBy(['user' => $user], ['createdAt' => 'DESC']);

        if (!$latestLog) {
            return new JsonResponse(['message' => 'No activity found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $latestLog->getId(),
            'type' => $latestLog->getActivityType()->value,
            'data' => $latestLog->getData(),
            'created_at' => $latestLog->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }
}
