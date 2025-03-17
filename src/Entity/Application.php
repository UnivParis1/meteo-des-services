<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource]
#[GetCollection]
#[ApiFilter(SearchFilter::class, properties: ['fname'])]
#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\OneToMany(targetEntity: ApplicationHistory::class, mappedBy: 'application')]
    private Collection $histories;

    #[ORM\OneToMany(targetEntity: Maintenance::class, mappedBy: 'application')]
    private Collection $maintenances;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: "SET NULL" )]
    private User|null $user = null;

    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\Column(length: 255, nullable: false)]
    private string $fname;

    #[ORM\Column(length: 255)]
    private ?string $Categorie = null;

    #[ORM\Column]
    private ?bool $isFromJson = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    public function __construct()
    {
        $this->histories = new ArrayCollection();
        $this->maintenances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, ApplicationHistory>
     */
    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function addHistory(ApplicationHistory $history): static
    {
        if (!$this->histories->contains($history)) {
            $this->histories->add($history);
            $history->setApplication($this);
        }

        return $this;
    }

    public function removeHistory(ApplicationHistory $history): static
    {
        if ($this->histories->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getApplication() === $this) {
                $history->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Maintenance>
     */
    public function getMaintenances(): Collection
    {
        return $this->maintenances;
    }

    public function addMaintenance(Maintenance $maintenance): static
    {
        if (!$this->maintenances->contains($maintenance)) {
            $this->maintenances->add($maintenance);
            $maintenance->setApplication($this);
        }

        return $this;
    }

    public function removeMaintenance(Maintenance $maintenance): static
    {
        if ($this->maintenances->removeElement($maintenance)) {
            // set the owning side to null (unless already changed)
            if ($maintenance->getApplication() === $this) {
                $maintenance->setApplication(null);
            }
        }

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        $histories = $this->getHistories();

        if (sizeof($histories) == 0)
            return null;

        $lastUpdate = $histories[0]->getDate();
        foreach ($histories as $history) {
            if ($lastUpdate == null || $history->getDate() > $lastUpdate) {
                $lastUpdate = $history->getDate();
            }
        }
        return $lastUpdate;
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
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(string $fname): static
    {
        $this->fname = $fname;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->Categorie;
    }

    public function setCategorie(string $Categorie): static
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function isFromJson(): ?bool
    {
        return $this->isFromJson;
    }

    public function setIsFromJson(bool $isFromJson): static
    {
        $this->isFromJson = $isFromJson;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }
}