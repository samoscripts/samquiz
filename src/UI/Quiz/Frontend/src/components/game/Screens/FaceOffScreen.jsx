import { useRef, useEffect } from 'react'
import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import AnswerBoard from '../AnswerBoard'
import QuestionInput from '../QuestionInput'
import ScoreBoard from '../ScoreBoard'
import ErrorMessage from '../../common/ErrorMessage'
import { useGameAlert } from '../../../hooks/useGameAlert'

function FaceOffScreen() {
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
  const activeTeamKey = teamsCollection?.activeTeamKey ?? null
  const activeTeam = activeTeamKey ? teamsCollection?.teams?.[activeTeamKey] : null

  const handleSetActiveTeam = async (teamKey) => {
    if (!gameId) return

    setLoading(true)
    setError(null)

    try {
      // Backend zwraca pełny stan gry z ustawionym activeTeamKey
      const gameData = await gameApi.setActiveTeam(gameId, teamKey)
      setGameState(gameData)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleAnswerSubmit = async (e) => {
    e.preventDefault()
    
    if (!answerInput.trim() || !gameId || !activeTeamKey) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Backend zwraca zaktualizowany stan gry
      const gameData = await gameApi.verifyAnswer(gameId, answerInput)
      
      // Alerty są obsługiwane globalnie w App.jsx
      
      // Aktualizujemy stan - backend decyduje o zmianie phase
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
    if (!loading && inputRef.current && activeTeamKey) {
      inputRef.current.focus()
    }
  }, [loading, activeTeamKey])

  return (
    <div className="face-off-screen">
      <h2>FACE OFF</h2>
      
      {question && (
        <div className="question-display">
          <h3>{question.text}</h3>
        </div>
      )}

      <ScoreBoard teamsCollection={teamsCollection} roundPoints={roundPoints} activeTeamKey={activeTeamKey} />

      <div className="strikes-container">
        <div className="strike-indicator-placeholder"></div>
        <AnswerBoard 
          answers={question?.answerCollection?.answers || []}
          revealedAnswers={question?.revealedAnswers?.answers || []}
        />
        <div className="strike-indicator-placeholder"></div>
      </div>

      <div className="face-off-controls">
        {activeTeamKey === null ? (
          <div className="team-selector">
            <p>Wybierz drużynę, która zaczyna:</p>
            {Object.keys(teamsCollection?.teams || {}).map((teamKey) => (
              <button
                key={teamKey}
                type="button"
                onClick={() => handleSetActiveTeam(teamKey)}
                className="team-select-btn"
                disabled={loading}
              >
                {teamsCollection?.teams[teamKey]?.name}
              </button>
            ))}
          </div>
        ) : (
          <div className="active-team-info">
            <p>Teraz gra: <strong>{activeTeam?.name || 'Brak drużyny'}</strong></p>
          </div>
        )}

        {activeTeamKey && (
          <form onSubmit={handleAnswerSubmit}>
            <QuestionInput
              ref={inputRef}
              value={answerInput}
              onChange={(e) => setAnswerInput(e.target.value.toUpperCase())}
              disabled={loading}
              placeholder="WPISZ ODPOWIEDŹ..."
              onSubmit={handleAnswerSubmit}
            />
            <button 
              type="submit" 
              className="submit-answer-btn"
              disabled={loading || !answerInput.trim()}
            >
              {loading ? 'Weryfikowanie...' : 'Wyślij'}
            </button>
          </form>
        )}
      </div>

      {error && <ErrorMessage message={error} />}
    </div>
  )
}

export default FaceOffScreen

