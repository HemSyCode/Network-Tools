<?php

namespace App\Entity;

use App\Repository\RirIpNetworkRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RirIpNetworkRepository::class)]
class RirIpNetwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 255)]
    private $handle;

    #[ORM\ManyToOne(targetEntity: Rir::class, inversedBy: 'rirIpNetworks')]
    #[ORM\JoinColumn(name: 'rir', referencedColumnName: 'code', nullable: false)]
    private $rir;

    #[ORM\Column(type: 'smallint')]
    private $ipVersion;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private $country;

    #[ORM\Column(type: 'string', length: 32)]
    private $status;

    #[ORM\Column(type: 'date_immutable')]
    private $allocatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private $ipStart;

    #[ORM\Column(type: 'string', length: 255)]
    private $ipEnd;

    #[ORM\Column(type: 'smallint')]
    private $cidr;

    #[ORM\Column(type: 'string', length: 255)]
    private $ipCount;

    #[ORM\Column(type: 'string', length: 255)]
    private $ipStartDec;

    #[ORM\Column(type: 'string', length: 255)]
    private $ipEndDec;

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

    public function getIpVersion(): ?int
    {
        return $this->ipVersion;
    }

    public function setIpVersion(int $ipVersion): self
    {
        $this->ipVersion = $ipVersion;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = strtoupper($country);
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = strtoupper($status);
        return $this;
    }

    public function getAllocatedAt(): ?DateTimeImmutable
    {
        return $this->allocatedAt;
    }

    public function setAllocatedAt(DateTimeImmutable $allocatedAt): self
    {
        $this->allocatedAt = $allocatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getIpStart(): ?string
    {
        return $this->ipStart;
    }

    public function setIpStart(string $ipStart): self
    {
        $this->ipStart = $ipStart;
        return $this;
    }

    public function getIpEnd(): ?string
    {
        return $this->ipEnd;
    }

    public function setIpEnd(string $ipEnd): self
    {
        $this->ipEnd = $ipEnd;
        return $this;
    }

    public function getCidr(): ?int
    {
        return $this->cidr;
    }

    public function setCidr(int $cidr): self
    {
        $this->cidr = $cidr;
        return $this;
    }

    public function getIpCount(): ?string
    {
        return $this->ipCount;
    }

    public function setIpCount(string $ipCount): self
    {
        $this->ipCount = $ipCount;
        return $this;
    }

    public function getIpStartDec(): ?string
    {
        return $this->ipStartDec;
    }

    public function setIpStartDec(string $ipStartDec): self
    {
        $this->ipStartDec = $ipStartDec;
        return $this;
    }

    public function getIpEndDec(): ?string
    {
        return $this->ipEndDec;
    }

    public function setIpEndDec(string $ipEndDec): self
    {
        $this->ipEndDec = $ipEndDec;
        return $this;
    }
}
