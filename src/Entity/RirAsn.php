<?php

namespace App\Entity;

use App\Repository\RirAsnRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RirAsnRepository::class)]
class RirAsn
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 255)]
    private $handle;

    #[ORM\ManyToOne(targetEntity: Rir::class, inversedBy: 'rirAsns')]
    #[ORM\JoinColumn(name: 'rir', referencedColumnName: 'code', nullable: false)]
    private $rir;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private $country;

    #[ORM\Column(type: 'string', length: 32)]
    private $status;

    #[ORM\Column(type: 'date_immutable')]
    private $allocatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $updatedAt;

    #[ORM\Column(type: 'integer')]
    private $asnStart;

    #[ORM\Column(type: 'integer')]
    private $asnEnd;

    #[ORM\Column(type: 'integer')]
    private $asnCount;

    public function __construct()
    {
        $this->updatedAt = new DateTimeImmutable('now');
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): self
    {
        $this->handle = $handle;
        return $this;
    }

    public function getRir(): ?Rir
    {
        return $this->rir;
    }

    public function setRir(?Rir $rir): self
    {
        $this->rir = $rir;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getAllocatedAt(): ?\DateTimeImmutable
    {
        return $this->allocatedAt;
    }

    public function setAllocatedAt(\DateTimeImmutable $allocatedAt): self
    {
        $this->allocatedAt = $allocatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getAsnStart(): ?int
    {
        return $this->asnStart;
    }

    public function setAsnStart(int $asnStart): self
    {
        $this->asnStart = $asnStart;
        return $this;
    }

    public function getAsnEnd(): ?int
    {
        return $this->asnEnd;
    }

    public function setAsnEnd(int $asnEnd): self
    {
        $this->asnEnd = $asnEnd;
        return $this;
    }

    public function getAsnCount(): ?int
    {
        return $this->asnCount;
    }

    public function setAsnCount(int $asnCount): self
    {
        $this->asnCount = $asnCount;
        return $this;
    }
}
