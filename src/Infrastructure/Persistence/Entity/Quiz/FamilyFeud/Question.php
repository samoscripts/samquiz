<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
// use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "ff_question")]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private string $text;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: Answer::class)]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: AnswerUser::class)]
    private Collection $userAnswers;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->userAnswers = new ArrayCollection();
    }

    // getters / setters ...
}
