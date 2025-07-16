<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\EventListener\UserListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\EntityListeners([UserListener::class])]
#[UniqueEntity(fields: ['uid'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_uid', fields: ['uid'])]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 180, unique: true)]
    private string $uid;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column]
    private bool $recevoirMail = false;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\ManyToMany(targetEntity: Application::class, mappedBy: 'users')]
    private Collection $applications;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eduPersonPrimaryAffiliation = null;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
    }

    public function __toString(): string {
        return $this->uid;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uid;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        if (count($roles) == 0) {
            // guarantee every user at least has ROLE_STUDENT
            $roles[] = 'ROLE_STUDENT';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function isRecevoirMail(): bool
    {
        return $this->recevoirMail;
    }

    public function setRecevoirMail(bool $recevoirMail): static
    {
        $this->recevoirMail = $recevoirMail;

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->addUser($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            $application->removeUser($this);
        }

        return $this;
    }

    public function getEduPersonPrimaryAffiliation(): ?string
    {
        return $this->eduPersonPrimaryAffiliation;
    }

    public function setEduPersonPrimaryAffiliation(?string $eduPersonPrimaryAffiliation): static
    {
        $this->eduPersonPrimaryAffiliation = $eduPersonPrimaryAffiliation;

        return $this;
    }
}
