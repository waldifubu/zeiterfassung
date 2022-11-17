<?php

namespace App\Entity;

use App\Repository\TimelogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimelogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Timelog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $start;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $end;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comment;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'timelogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Gets triggered only on insert
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new \DateTime("now");
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

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Gets triggered every time on update
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new \DateTime("now");
    }
}
