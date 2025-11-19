<?php

namespace App\Entity;

use App\Repository\TestSamoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestSamoRepository::class)]
class TestSamo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $yes = null;

    #[ORM\Column]
    private ?\DateTime $createdate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getYes(): ?string
    {
        return $this->yes;
    }

    public function setYes(string $yes): static
    {
        $this->yes = $yes;

        return $this;
    }

    public function getCreatedate(): ?\DateTime
    {
        return $this->createdate;
    }

    public function setCreatedate(\DateTime $createdate): static
    {
        $this->createdate = $createdate;

        return $this;
    }
}
