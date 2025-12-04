<?php

namespace App\Domain\Quiz\FamilyFeud\Entity;

use App\Domain\Quiz\FamilyFeud\ValueObject\Answer;
use Symfony\Component\Serializer\Attribute\Groups;

final class GameAnswer extends Answer
{
    public function __construct(
        #[Groups(['public'])]
        readonly public string $text,
        #[Groups(['public'])]
        readonly public int $points,
        #[Groups(['public'])]
        readonly public ?int $id = null
    ) {
        parent::__construct($text, $points, $id);
    }


}