<?php

namespace App\Entity;

use App\Enum\ActivityType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activity_log', schema: 'public')]
#[ORM\Index(name: 'idx_activity_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_activity_type', columns: ['type'])]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private readonly User $user;

    #[ORM\Column(type: 'string', length: 255, enumType: ActivityType::class)]
    private readonly ActivityType $type;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $steps = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $distanceKm = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $accuracyMeters = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = [];

    #[ORM\Column(type: 'datetimetz_immutable', options: ['default' => 'NOW()'])]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        User         $user,
        ActivityType $type,
        ?int         $steps = null,
        ?float       $distanceKm = null,
        ?float       $latitude = null,
        ?float       $longitude = null,
        ?float       $accuracyMeters = null,
        ?array       $metadata = []
    ) {
        $this->user = $user;
        $this->type = $type;
        $this->steps = $steps;
        $this->distanceKm = $distanceKm;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->accuracyMeters = $accuracyMeters;
        $this->metadata = $metadata;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): ActivityType
    {
        return $this->type;
    }

    public function getSteps(): ?int
    {
        return $this->steps;
    }

    public function getDistanceKm(): ?float
    {
        return $this->distanceKm;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getAccuracyMeters(): ?float
    {
        return $this->accuracyMeters;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
