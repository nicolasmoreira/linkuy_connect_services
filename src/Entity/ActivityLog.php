<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activity_log', schema: 'public')]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private readonly int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $user;

    #[ORM\Column(type: 'string', length: 255, enumType: ActivityType::class)]
    private readonly ActivityType $activityType;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        ActivityType $activityType,
        array $metadata = []
    ) {
        $this->user = $user;
        $this->activityType = $activityType;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getActivityType(): ActivityType { return $this->activityType; }
    public function getMetadata(): array { return $this->metadata; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
