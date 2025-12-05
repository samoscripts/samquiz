import { useRef, useEffect } from 'react'
import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import AnswerBoard from '../AnswerBoard'
import QuestionInput from '../QuestionInput'
import ScoreBoard from '../ScoreBoard'
import StrikeIndicator from '../StrikeIndicator'
import ErrorMessage from '../../common/ErrorMessage'
import { useGameAlert } from '../../../hooks/useGameAlert'

function PlayingScreen() {
  const inputRef = useRef(null)
  const game = useGameStore(state => state.game)
  const gameAlert = game?.gameAlert
  const { handleGameAlert } = useGameAlert()

  // Obsługa alertów - można rozszerzyć w przyszłości
  useEffect(() => {
    if (gameAlert) {
      handleGameAlert(gameAlert)
    }
  }, [gameAlert, handleGameAlert])

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
      
      // Alerty są obsługiwane globalnie w App.jsx
      
      // Backend może zmienić phase na STEAL, END_ROUND, lub zostać w PLAYING
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
    <div className="playing-screen">
      {question && (
        <div className="question-display">
          <h3>{question.text}</h3>
        </div>
      )}

      <ScoreBoard teamsCollection={teamsCollection} roundPoints={roundPoints} activeTeamKey={activeTeamKey} />

      <div className="strikes-container">
        <StrikeIndicator strikes={teamsCollection?.teams?.["1"]?.strikes || 0} />
        <AnswerBoard 
          answers={question?.answerCollection?.answers || []}
          revealedAnswers={question?.revealedAnswers?.answers || []}
        />
        <StrikeIndicator strikes={teamsCollection?.teams?.["2"]?.strikes || 0} />
      </div>

      <div className="playing-controls">
        <div className="active-team-info">
          <p>Teraz gra: <strong>{activeTeam?.name || 'Brak drużyny'}</strong></p>
          <p>Błędy: {activeTeam?.strikes || 0} / 3</p>
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

export default PlayingScreen

