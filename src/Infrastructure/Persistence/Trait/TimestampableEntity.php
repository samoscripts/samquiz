<?php

namespace App\Infrastructure\Persistence\Trait;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableEntity
{

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
}

