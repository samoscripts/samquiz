<?php

namespace App\Domain\Quiz\FamilyFeud\Entity\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
// use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "ff_question")]
class FfQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private string $text;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: FfAnswer::class)]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: FfAnswerUser::class)]
    private Collection $userAnswers;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->userAnswers = new ArrayCollection();
    }

    // getters / setters ...
}
