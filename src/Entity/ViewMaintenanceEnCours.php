<?php

namespace App\Entity;

use App\Repository\MaintenanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class, readOnly: true)]
#[ORM\Table(name: 'view_maintenance_encours')]
class ViewMaintenanceEnCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'maintenances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $application = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startingDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endingDate = null;

    #[ORM\Column(length: 255)]
    private ?string $applicationState = null;

    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\OneToMany(targetEntity: MaintenanceHistory::class, mappedBy: 'Maintenance')]
    private Collection $maintenanceHistories;

    private function __construct()
    {
        $this->maintenanceHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function getStartingDate(): ?\DateTimeInterface
    {
        return $this->startingDate;
    }

    public function getEndingDate(): ?\DateTimeInterface
    {
        return $this->endingDate;
    }

    public function getApplicationState(): ?string
    {
        return $this->applicationState;
    }

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    /**
     * @return Collection<int, MaintenanceHistory>
     */
    public function getMaintenanceHistories(): Collection
    {
        return $this->maintenanceHistories;
    }
}
