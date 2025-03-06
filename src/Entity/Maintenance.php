<?php

namespace App\Entity;

use App\Repository\MaintenanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class)]
class Maintenance
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Message = null;

    public function __construct()
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

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }

    public function getStartingDate(): ?\DateTimeInterface
    {
        return $this->startingDate;
    }

    public function setStartingDate(\DateTimeInterface $startingDate): static
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    public function getEndingDate(): ?\DateTimeInterface
    {
        return $this->endingDate;
    }

    public function setEndingDate(\DateTimeInterface $endingDate): static
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    public function getApplicationState(): ?string
    {
        return $this->applicationState;
    }

    public function setApplicationState(string $applicationState): static
    {
        $this->applicationState = $applicationState;

        return $this;
    }

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * @return Collection<int, MaintenanceHistory>
     */
    public function getMaintenanceHistories(): Collection
    {
        return $this->maintenanceHistories;
    }

    public function addMaintenanceHistory(MaintenanceHistory $maintenanceHistory): static
    {
        if (!$this->maintenanceHistories->contains($maintenanceHistory)) {
            $this->maintenanceHistories->add($maintenanceHistory);
            $maintenanceHistory->setMaintenance($this);
        }

        return $this;
    }

    public function removeMaintenanceHistory(MaintenanceHistory $maintenanceHistory): static
    {
        if ($this->maintenanceHistories->removeElement($maintenanceHistory)) {
            // set the owning side to null (unless already changed)
            if ($maintenanceHistory->getMaintenance() === $this) {
                $maintenanceHistory->setMaintenance(null);
            }
        }

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->Message;
    }

    public function setMessage(?string $Message): static
    {
        $this->Message = $Message;

        return $this;
    }
}
