<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'projects')]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[UniqueEntity(fields: 'name', message: 'Name is already taken.')]
#[ORM\HasLifecycleCallbacks]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $name;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Timelog::class, orphanRemoval: true)]
    private Collection $timelogs;

    public function __construct()
    {
        $this->timelogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTimelogs(): Collection
    {
        return $this->timelogs;
    }

    public function addTimelog(Timelog $timelog): self
    {
        if (!$this->timelogs->contains($timelog)) {
            $this->timelogs[] = $timelog;
            $timelog->setProject($this);
        }

        return $this;
    }

    public function removeTimelog(Timelog $timelog): self
    {
        // set the owning side to null (unless already changed)
        if ($this->timelogs->removeElement($timelog) && $timelog->getProject() === $this) {
            $timelog->setProject(null);
        }

        return $this;
    }

    /**
     * Gets triggered only on insert
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new \DateTime();
    }
}
