<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use Symfony\Component\Serializer\Attribute\Groups;

final class GameAnswer
{
    public function __construct(
        #[Groups(['public'])]
         public string $text,
        #[Groups(['public'])]
         public int $points,
        #[Groups(['public'])]
         public ?int $id = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'points' => $this->points,
            'id' => $this->id,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }


    


}