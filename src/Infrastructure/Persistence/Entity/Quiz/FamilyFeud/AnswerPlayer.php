<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use App\Infrastructure\Persistence\Trait\TimestampableEntity;
use App\Infrastructure\Persistence\Trait\IdEntity;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Quiz\FamilyFeud\ValueObject\PlayerAnswer as DomainPlayerAnswer;

#[ORM\Entity]
#[ORM\Table(name: "ff_answer_player")]
class AnswerPlayer
{
    use TimestampableEntity;
    use IdEntity;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: "playerAnswers")]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(length: 255)]
    private string $player_text;

    #[ORM\ManyToOne(targetEntity: Answer::class, inversedBy: "playerAnswers")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Answer $answer = null;

    #[ORM\Column(type: "boolean")]
    private bool $is_correct;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getPlayerText(): string
    {
        return $this->player_text;
    }

    public function setPlayerText(string $player_text): self
    {
        $this->player_text = $player_text;
        return $this;
    }

    public function getIsCorrect(): bool
    {
        return $this->is_correct;
    }

    public function setIsCorrect(bool $is_correct): self
    {
        $this->is_correct = $is_correct;
        return $this;
    }

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(Answer $answer): self
    {
        $this->answer = $answer;
        return $this;
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

    public function toDomain(): DomainPlayerAnswer
    {
        $domainAnswer = $this->answer?->toDomain();
        $domainQuestion = $this->question->toDomain();
        return new DomainPlayerAnswer(
            $this->player_text,
            $domainAnswer,
            $this->is_correct,
            $domainQuestion
        );
    }

    public static function fromDomain(
        DomainPlayerAnswer $domainPlayerAnswer
        ): self
    {
        $answerPlayer = new self();
        $answerPlayer->setPlayerText($domainPlayerAnswer->getPlayerText());
        $answerPlayer->setIsCorrect($domainPlayerAnswer->isCorrect());

        // $answerPlayer->setQuestion($domainPlayerAnswer->);
        // $DoctrineAnswer ? $answerPlayer->setAnswer($DoctrineAnswer) : null;
        return $answerPlayer;
    }
}
