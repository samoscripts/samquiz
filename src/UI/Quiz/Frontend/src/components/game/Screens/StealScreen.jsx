import { useRef, useEffect } from 'react'
import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import AnswerBoard from '../AnswerBoard'
import QuestionInput from '../QuestionInput'
import ScoreBoard from '../ScoreBoard'
import SingleStrikeIndicator from '../SingleStrikeIndicator'
import ErrorMessage from '../../common/ErrorMessage'

function StealScreen() {
  const inputRef = useRef(null)
  const game = useGameStore(state => state.game)
  const { 
    answerInput,
    setAnswerInput,
    setGameState,
    setLoading,
    setError,
    loading,
    error 
  } = useGameStore()

  const gameId = game?.gameId
  const teamsCollection = game?.teamsCollection
  const question = game?.question
  const roundPoints = game?.roundPoints
  const activeTeamKey = teamsCollection?.activeTeamKey || "1"
  const activeTeam = teamsCollection?.teams?.[activeTeamKey] || teamsCollection?.teams?.["1"]
  const opponentTeamKey = activeTeamKey === "1" ? "2" : "1"
  const opponentTeam = teamsCollection?.teams?.[opponentTeamKey] || teamsCollection?.teams?.["2"]
  const pointsToSteal = roundPoints || 0

  const handleAnswerSubmit = async (e) => {
    e.preventDefault()
    
    if (!answerInput.trim() || !gameId) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Backend przetwarza odpowiedź i zwraca zaktualizowany stan
      const gameData = await gameApi.verifyAnswer(gameId, answerInput)
      
      // Backend zmieni phase na END_ROUND po kradzieży
      setGameState(gameData)
      setAnswerInput('')
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  // Auto-focus po zakończeniu ładowania
  useEffect(() => {
    if (!loading && inputRef.current) {
      inputRef.current.focus()
    }
  }, [loading])

  return (
    <div className="steal-screen">
      {question && (
        <div className="question-display">
          <h3>{question.text}</h3>
        </div>
      )}

      <ScoreBoard teamsCollection={teamsCollection} roundPoints={roundPoints} activeTeamKey={activeTeamKey} />

      <div className="strikes-container">
        <SingleStrikeIndicator strikes={teamsCollection?.teams?.["1"]?.strikes || 0} />
        <AnswerBoard 
          answers={question?.answerCollection?.answers || []}
          revealedAnswers={question?.revealedAnswers?.answers || []}
        />
        <SingleStrikeIndicator strikes={teamsCollection?.teams?.["2"]?.strikes || 0} />
      </div>

      <div className="steal-controls">
        <div className="steal-info">
          <h3>
            {activeTeam?.name || 'Drużyna'} ma szansę ukraść punkty!
          </h3>
          <p>{pointsToSteal} pkt do kradzieży</p>
          <p className="steal-hint">Jedna odpowiedź - jeśli trafisz, kradniesz wszystkie punkty!</p>
        </div>

        <form onSubmit={handleAnswerSubmit}>
          <QuestionInput
            ref={inputRef}
            value={answerInput}
            onChange={(e) => setAnswerInput(e.target.value.toUpperCase())}
            disabled={loading}
            placeholder="WPISZ ODPOWIEDŹ..."
            onSubmit={handleAnswerSubmit}
          />
        </form>
      </div>

      {error && <ErrorMessage message={error} />}
    </div>
  )
}

export default StealScreen

