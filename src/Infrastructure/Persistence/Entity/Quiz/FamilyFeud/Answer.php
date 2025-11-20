<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "ff_answer")]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "answers")]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(length: 255)]
    private string $text;

    #[ORM\Column]
    private int $points;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\OneToMany(mappedBy: "answer", targetEntity: AnswerUser::class)]
    private Collection $userAnswers;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->userAnswers = new ArrayCollection();
    }

    // getters / setters ...
}
