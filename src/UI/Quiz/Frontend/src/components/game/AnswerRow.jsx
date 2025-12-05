function AnswerRow({ answer, answerNumber, isRevealed, isLast, onClick }) {
  return (
    <div 
      className={`answer-row ${isRevealed ? 'revealed' : 'hidden'} ${isLast ? 'last-answer' : ''} ${onClick ? 'clickable' : ''}`}
      onClick={onClick}
    >
      <span className="answer-number">{answerNumber}</span>
      
      {isRevealed ? (
        <>
          <span className="answer-text">{answer.text}</span>
          <span className="answer-points">{answer.points}</span>
        </>
      ) : (
        <span className="answer-text hidden-text">•••••••••••••••••••••••••••••••</span>
      )}
    </div>
  )
}

export default AnswerRow

