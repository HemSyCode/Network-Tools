<?php

namespace App\Entity;

use App\Repository\RirRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RirRepository::class)]
class Rir
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private $code;

    #[ORM\Column(type: 'string', length: 10)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $fullName;

    #[ORM\Column(type: 'string', length: 255)]
    private $website;

    #[ORM\Column(type: 'string', length: 64)]
    private $whoisServer;

    #[ORM\Column(type: 'string', length: 255)]
    private $radpServerUrl;

    #[ORM\Column(type: 'string', length: 255)]
    private $allocationListUrl;

    public function __construct(){}

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function getWhoisServer(): ?string
    {
        return $this->whoisServer;
    }

    public function setWhoisServer(string $whoisServer): self
    {
        $this->whoisServer = $whoisServer;
        return $this;
    }

    public function getRadpServerUrl(): ?string
    {
        return $this->radpServerUrl;
    }

    public function setRadpServerUrl(string $radpServerUrl): self
    {
        $this->radpServerUrl = $radpServerUrl;
        return $this;
    }

    public function getAllocationListUrl(): ?string
    {
        return $this->allocationListUrl;
    }

    public function setAllocationListUrl(string $allocationListUrl): self
    {
        $this->allocationListUrl = $allocationListUrl;
        return $this;
    }
}
