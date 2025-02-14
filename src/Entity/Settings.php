<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'settings', schema: 'public')]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Family::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly Family $family;

    #[ORM\Column(type: 'smallint', options: ['default' => 30])]
    private int $inactivityThreshold = 30;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $doNotDisturb = false;

    public function __construct(Family $family)
    {
        $this->family = $family;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamily(): Family
    {
        return $this->family;
    }

    public function getInactivityThreshold(): int
    {
        return $this->inactivityThreshold;
    }

    public function setInactivityThreshold(int $threshold): void
    {
        $this->inactivityThreshold = $threshold;
    }

    public function isDoNotDisturb(): bool
    {
        return $this->doNotDisturb;
    }

    public function setDoNotDisturb(bool $value): void
    {
        $this->doNotDisturb = $value;
    }
}
