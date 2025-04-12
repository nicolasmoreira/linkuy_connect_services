<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ActivityLog;
use App\Entity\User;
use App\Enum\ActivityType;
use App\Enum\UserType;
use App\Repository\ActivityLogRepository;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Activity Log')]
final class ActivityLogController extends AbstractController
{
    use ApiResponseTrait;

    private const MAX_ACTIVITIES = 200;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ActivityLogRepository $activityLogRepository
    ) {}

    /**
     * Get activity logs from monitored seniors.
     */
    #[OA\Response(
        response: 200,
        description: 'Activity logs retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'type', type: 'string', example: 'FALL_DETECTED'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'user', type: 'object', example: ['id' => 2]),
                        ],
                    ),
                ),
            ],
        ),
    )]
    #[Route('/api/activity-log', name: 'activity_log_list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $seniors = $this->entityManager->getRepository(User::class)
            ->findBy(['family' => $user->getFamily(), 'userType' => UserType::SENIOR]);

        if (empty($seniors)) {
            return $this->notFound('No se encontraron adultos mayores monitoreados');
        }

        $activityLogs = [];
        foreach ($seniors as $senior) {
            $seniorLogs = $this->entityManager->getRepository(ActivityLog::class)
                ->findBy(['user' => $senior], ['createdAt' => 'DESC'], self::MAX_ACTIVITIES);

            foreach ($seniorLogs as $log) {
                $activityLogs[] = [
                    'id' => $log->getId(),
                    'type' => $log->getType()->value,
                    'created_at' => $log->getCreatedAt()->format('c'),
                    'user' => [
                        'id' => $senior->getId(),
                    ],
                ];
            }
        }

        return $this->success($activityLogs);
    }

    /**
     * Get last known location of monitored seniors.
     */
    #[OA\Response(
        response: 200,
        description: 'Last known locations retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 40.4168),
                            new OA\Property(property: 'longitude', type: 'number', format: 'float', example: -3.7038),
                            new OA\Property(property: 'accuracy_meters', type: 'number', format: 'float', example: 10.5),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'user', type: 'object', example: ['id' => 2]),
                        ],
                    ),
                ),
            ],
        ),
    )]
    #[Route('/api/activity-log/locations', name: 'activity_log_locations', methods: ['GET'])]
    public function locations(#[CurrentUser] User $user): JsonResponse
    {
        $seniors = $this->entityManager->getRepository(User::class)
            ->findBy(['family' => $user->getFamily(), 'userType' => UserType::SENIOR]);

        if (empty($seniors)) {
            return $this->notFound('No se encontraron adultos mayores monitoreados');
        }

        $locations = [];
        foreach ($seniors as $senior) {
            $lastLocation = $this->activityLogRepository->findLatestByUser($senior, ActivityType::LOCATION_UPDATE);

            if ($lastLocation) {
                $locations[] = [
                    'id' => $lastLocation->getId(),
                    'latitude' => $lastLocation->getLatitude(),
                    'longitude' => $lastLocation->getLongitude(),
                    'accuracy_meters' => $lastLocation->getAccuracyMeters(),
                    'created_at' => $lastLocation->getCreatedAt()->format('c'),
                    'user' => [
                        'id' => $senior->getId(),
                    ],
                ];
            }
        }

        return $this->success($locations);
    }
}
