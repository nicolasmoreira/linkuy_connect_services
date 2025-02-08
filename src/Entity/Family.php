<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'family')]
class Family
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private readonly int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'family', targetEntity: User::class, cascade: ['persist', 'remove'])]
    private Collection $users;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->createdAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): void { $this->active = $active; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUsers(): Collection { return $this->users; }
    public function addUser(User $user): void { $this->users->add($user); }
    public function removeUser(User $user): void { $this->users->removeElement($user); }
}
