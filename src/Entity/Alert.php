<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'alert', schema: 'public')]
class Alert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private readonly int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $user;

    #[ORM\Column(type: 'string', length: 255, enumType: AlertType::class)]
    private readonly AlertType $alertType;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $sent = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(User $user, AlertType $alertType)
    {
        $this->user = $user;
        $this->alertType = $alertType;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getAlertType(): AlertType { return $this->alertType; }
    public function isSent(): bool { return $this->sent; }
    public function markAsSent(): void { $this->sent = true; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
