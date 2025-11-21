<?php

namespace App\Infrastructure\Persistence\Entity\Quiz\FamilyFeud;

use App\Infrastructure\Persistence\Trait\TimestampableEntity;
use App\Infrastructure\Persistence\Trait\IdEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Quiz\FamilyFeud\Entity\Question as DomainQuestion;

#[ORM\Entity]
#[ORM\Table(name: "ff_question")]
class Question
{
    use TimestampableEntity;
    use IdEntity;

    #[ORM\Column(length: 500)]
    private string $text;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: Answer::class)]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: "question", targetEntity: AnswerPlayer::class)]
    private Collection $playerAnswers;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->playerAnswers = new ArrayCollection();
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

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, PlayerAnswer>
     */
    public function getPlayerAnswers(): Collection
    {
        return $this->playerAnswers;
    }

    public function toDomain(): DomainQuestion
    {
        $domainAnswers = [];
        foreach ($this->answers as $answer) {
            $domainAnswers[] = $answer->toDomain();
        }
        $domainQuestion = new DomainQuestion($this->text, $domainAnswers, $this->id);
        
        return $domainQuestion;
    }

    public static function fromDomain(DomainQuestion $domainQuestion): self
    {
        $question = new self();
        $question->setText($domainQuestion->text());
        foreach ($domainQuestion->answers() as $domainAnswer) {
            $question->addAnswer(Answer::fromDomain($domainAnswer));
        }

        return $question;
    }
}
