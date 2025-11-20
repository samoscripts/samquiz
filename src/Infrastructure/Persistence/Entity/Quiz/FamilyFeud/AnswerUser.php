<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "ff_answer_user")]
class AnswerUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "userAnswers")]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(length: 255)]
    private string $user_text;

    #[ORM\ManyToOne(targetEntity: Answer::class, inversedBy: "userAnswers")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Answer $answer = null;

    #[ORM\Column(type: "boolean")]
    private bool $is_correct;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    // getters / setters ...
}
