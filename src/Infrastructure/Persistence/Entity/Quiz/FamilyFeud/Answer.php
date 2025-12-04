<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use App\Infrastructure\Persistence\Trait\TimestampableEntity;
use App\Infrastructure\Persistence\Trait\IdEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Quiz\FamilyFeud\ValueObject\Answer as DomainAnswer;

#[ORM\Entity]
#[ORM\Table(name: "ff_answer")]
class Answer
{
    use TimestampableEntity;
    use IdEntity;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "answers")]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(length: 255)]
    private string $text;

    #[ORM\Column]
    private int $points;

    #[ORM\OneToMany(mappedBy: "answer", targetEntity: AnswerPlayer::class)]
    private Collection $answerPlayers;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->answerPlayers = new ArrayCollection();
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    /**
     * @return Collection<int, AnswerPlayer>
     */
    public function getAnswerPlayers(): Collection
    {
        return $this->answerPlayers;
    }

    public function toDomain(): DomainAnswer
    {
        return new DomainAnswer(
            $this->text,
            $this->points,
            $this->id
        );
    }

    public static function fromDomain(DomainAnswer $domainAnswer): self
    {
        $answer = new self();
        $answer->setText($domainAnswer->text());
        $answer->setPoints($domainAnswer->getPoints());
        return $answer;
    }
}
