import AnswerRow from './AnswerRow'

function AnswerBoard({ answers, revealedAnswers, onAnswerClick, disabled }) {
  if (!answers || answers.length === 0) {
    return <div className="answer-board empty">Brak odpowiedzi</div>
  }

  // revealedAnswers to Set lub Array z backendu
  const revealedSet = new Set(
    Array.isArray(revealedAnswers) 
      ? revealedAnswers.map(a => a.text || a)
      : []
  )

  return (
    <div className="answer-board">
      {answers.map((answer, index) => {
        const isRevealed = revealedSet.has(answer.text)
        const isLast = index === answers.length - 1
        // Numer odpowiedzi to pozycja w oryginalnej tablicy (indeks + 1)
        // Nie zależy od kolejności odkrycia
        const answerNumber = index + 1
        
        // Obsługa kliknięcia - tylko dla nieodkrytych odpowiedzi i gdy onAnswerClick jest przekazane
        const handleClick = !isRevealed && onAnswerClick && !disabled
          ? () => onAnswerClick(answer.text)
          : null
        
        return (
          <AnswerRow
            key={index}
            answer={answer}
            answerNumber={answerNumber}
            isRevealed={isRevealed}
            isLast={isLast}
            onClick={handleClick}
          />
        )
      })}
    </div>
  )
}

export default AnswerBoard

