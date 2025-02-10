<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification', schema: 'public')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private readonly int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly User $recipient;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $sent = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly \DateTimeImmutable $createdAt;

    public function __construct(User $recipient, string $message)
    {
        $this->recipient = $recipient;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getRecipient(): User { return $this->recipient; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): void { $this->message = $message; }
    public function isSent(): bool { return $this->sent; }
    public function markAsSent(): void { $this->sent = true; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
