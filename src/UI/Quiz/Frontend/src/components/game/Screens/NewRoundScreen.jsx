import { useState, useEffect } from 'react'
import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import Button from '../../common/Button'
import Input from '../../common/Input'
import ErrorMessage from '../../common/ErrorMessage'
import LoadingSpinner from '../../common/LoadingSpinner'
import { useGameAlert } from '../../../hooks/useGameAlert'

function NewRoundScreen() {
  const game = useGameStore(state => state.game)
  const gameAlert = game?.gameAlert
  const { handleGameAlert } = useGameAlert()
  const { 
    setGameState,
    setLoading,
    setError,
    loading,
    error 
  } = useGameStore()

  // Obsługa alertów - można rozszerzyć w przyszłości
  useEffect(() => {
    if (gameAlert) {
      handleGameAlert(gameAlert)
    }
  }, [gameAlert, handleGameAlert])
  
  const gameId = game?.gameId
  const currentRound = game?.currentRound

  const [questionText, setQuestionText] = useState('Popularne kobiece imiona')
  const [answersCount, setAnswersCount] = useState(5)

  const handleCreateNewRound = async (e) => {
    e.preventDefault()
    
    if (!questionText.trim() || !gameId) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Generujemy pytanie dla nowej rundy
      // Backend zwraca pełny stan gry z phase: 'FACE_OFF'
      const gameData = await gameApi.createNewRound(gameId, questionText.trim(), answersCount)
      
      // Aktualizujemy stan dokładnie tym, co zwrócił backend
      setGameState(gameData)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <LoadingSpinner message="Generowanie pytania..." />
  }

  return (
    <div className="new-round-screen">
      <h1>Runda {currentRound}</h1>
      
      <form onSubmit={handleCreateNewRound} className="new-round-form">
        <Input
          label="Pytanie"
          value={questionText}
          onChange={(e) => setQuestionText(e.target.value)}
          disabled={loading}
          placeholder="Popularne kobiece imiona"
          required
        />
        
        <div className="form-group">
          <label htmlFor="answersCount">Liczba odpowiedzi</label>
          <select
            id="answersCount"
            value={answersCount}
            onChange={(e) => setAnswersCount(parseInt(e.target.value))}
            disabled={loading}
            className="form-select"
            required
          >
            {[3, 4, 5, 6, 7].map(num => (
              <option key={num} value={num}>
                {num} {num === 1 ? 'odpowiedź' : num < 5 ? 'odpowiedzi' : 'odpowiedzi'}
              </option>
            ))}
          </select>
        </div>
        
        <Button type="submit" disabled={loading || !questionText.trim()}>
          {loading ? 'Generowanie...' : 'Generuj pytanie'}
        </Button>
      </form>

      {error && <ErrorMessage message={error} />}
    </div>
  )
}

export default NewRoundScreen

