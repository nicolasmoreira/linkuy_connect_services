<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActivityLog;
use App\Enum\ActivityType;
use App\Enum\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 *
 * @method ActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityLog[]    findAll()
 * @method ActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    public function findLatestByUser($user, ?ActivityType $type = null): ?ActivityLog
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user);

        if ($type) {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        if ($type === ActivityType::LOCATION_UPDATE) {
            $qb->andWhere('a.latitude IS NOT NULL')
                ->andWhere('a.longitude IS NOT NULL');
        }

        return $qb->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<array{user_id: int, inactivity_minutes: float, family_id: int, inactivity_threshold: int}>
     * @throws Exception
     */
    public function findInactiveUsers(int $defaultThreshold): array
    {
        $sql = <<<SQL
                WITH last_activities AS (
                    SELECT
                        al.user_id,
                        al.created_at,
                        EXTRACT(EPOCH FROM (NOW() - al.created_at))/60 as inactivity_minutes,
                        u.family_id,
                        COALESCE(s.inactivity_threshold, :default_threshold) as inactivity_threshold,
                        COALESCE(s.do_not_disturb, false) as do_not_disturb,
                        s.do_not_disturb_start_time,
                        s.do_not_disturb_end_time
                    FROM activity_log al
                    JOIN "user" u ON u.id = al.user_id
                    LEFT JOIN settings s ON s.family_id = u.family_id
                    WHERE u.user_type = :user_type
                    AND u.active = true
                    AND al.created_at = (
                        SELECT MAX(created_at)
                        FROM activity_log
                        WHERE user_id = al.user_id
                    )
                )
                SELECT
                    la.user_id,
                    la.inactivity_minutes,
                    la.family_id,
                    la.inactivity_threshold
                FROM last_activities la
                WHERE la.inactivity_minutes > la.inactivity_threshold
                AND (
                    la.do_not_disturb = false
                    OR (
                        la.do_not_disturb = true
                        AND (
                            la.do_not_disturb_start_time IS NULL
                            OR la.do_not_disturb_end_time IS NULL
                            OR NOT (
                                CURRENT_TIME BETWEEN la.do_not_disturb_start_time AND la.do_not_disturb_end_time
                            )
                        )
                    )
                )
            SQL;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('user_type', UserType::SENIOR->value);
        $stmt->bindValue('default_threshold', $defaultThreshold, ParameterType::INTEGER);

        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
