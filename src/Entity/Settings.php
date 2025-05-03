<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'settings', schema: 'public')]
class Settings
{
    use TimestampableEntity;

    public const DEFAULT_INACTIVITY_THRESHOLD = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['autoincrement' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Family::class)]
    #[ORM\JoinColumn(nullable: false)]
    private readonly Family $family;

    #[ORM\Column(type: 'smallint', options: ['default' => self::DEFAULT_INACTIVITY_THRESHOLD])]
    private int $inactivityThreshold = self::DEFAULT_INACTIVITY_THRESHOLD;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $doNotDisturbStartTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $doNotDisturbEndTime = null;

    // dnd
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

    public function setInactivityThreshold(int $threshold): self
    {
        $this->inactivityThreshold = $threshold;

        return $this;
    }

    public function setDoNotDisturbStartTime(?\DateTimeInterface $time): self
    {
        $this->doNotDisturbStartTime = $time;

        return $this;
    }

    public function getDoNotDisturbStartTime(): ?\DateTimeInterface
    {
        return $this->doNotDisturbStartTime;
    }

    public function setDoNotDisturbEndTime(?\DateTimeInterface $time): self
    {
        $this->doNotDisturbEndTime = $time;

        return $this;
    }

    public function getDoNotDisturbEndTime(): ?\DateTimeInterface
    {
        return $this->doNotDisturbEndTime;
    }

    public function isDoNotDisturb(): bool
    {
        return $this->doNotDisturb;
    }

    public function setDoNotDisturb(bool $value): self
    {
        $this->doNotDisturb = $value;

        return $this;
    }
}
