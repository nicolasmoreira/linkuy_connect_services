<?php

namespace App\Entity;

use App\Enum\UserType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user', schema: 'public')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', enumType: UserType::class)]
    private UserType $userType;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $active = false;

    #[ORM\ManyToOne(targetEntity: Family::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private Family $family;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $deviceToken = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        string   $email,
        string   $password,
        UserType $userType,
        Family   $family
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->userType = $userType;
        $this->family = $family;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    public function getFamily(): Family
    {
        return $this->family;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeviceToken(): ?string
    {
        return $this->deviceToken;
    }

    public function setDeviceToken(?string $deviceToken): void
    {
        $this->deviceToken = $deviceToken;
    }

    public function getRoles(): array
    {
        return [$this->userType->value];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}
