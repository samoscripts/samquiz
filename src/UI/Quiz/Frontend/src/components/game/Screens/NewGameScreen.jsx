import { useState, useEffect } from 'react'
import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import Button from '../../common/Button'
import Input from '../../common/Input'
import ErrorMessage from '../../common/ErrorMessage'
import LoadingSpinner from '../../common/LoadingSpinner'
import { useGameAlert } from '../../../hooks/useGameAlert'
import { useGameIdFromUrl } from '../../../hooks/useGameIdFromUrl'

function NewGameScreen() {
  const [team1Name, setTeam1Name] = useState('Drużyna 1')
  const [team2Name, setTeam2Name] = useState('Drużyna 2')
  const [roundsCount, setRoundsCount] = useState(3) // Dodane: domyślnie 3 rundy
  const { setGameState, setLoading, setError, loading, error, game } = useGameStore()
  const gameAlert = game?.gameAlert
  const { handleGameAlert } = useGameAlert()
  const { updateUrlWithGameId } = useGameIdFromUrl()

  // Obsługa alertów - można rozszerzyć w przyszłości
  useEffect(() => {
    if (gameAlert) {
      handleGameAlert(gameAlert)
    }
  }, [gameAlert, handleGameAlert])

  const handleStart = async (e) => {
    e.preventDefault()
    
    if (!team1Name.trim() || !team2Name.trim()) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Wywołanie API - backend zwraca pełny stan gry
      const gameData = await gameApi.createGame(team1Name.trim(), team2Name.trim(), roundsCount)
      
      // Zapisujemy dokładnie to, co zwrócił backend
      setGameState(gameData)
      
      // Aktualizuj URL z gameId (tylko dla przeglądarki)
      updateUrlWithGameId(gameData.gameId)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <LoadingSpinner message="Tworzenie gry..." />
  }

  return (
    <div className="start-screen">
      <h1>🎯 Quiz Familiada</h1>
      
      <form onSubmit={handleStart} className="start-form">
        <Input
          label="Nazwa drużyny 1"
          value={team1Name}
          onChange={(e) => setTeam1Name(e.target.value)}
          disabled={loading}
          required
        />
        
        <Input
          label="Nazwa drużyny 2"
          value={team2Name}
          onChange={(e) => setTeam2Name(e.target.value)}
          disabled={loading}
          required
        />
        
        {/* Dodane: Pole wyboru liczby rund */}
        <div className="form-group">
          <label htmlFor="roundsCount">Liczba rund</label>
          <select
            id="roundsCount"
            value={roundsCount}
            onChange={(e) => setRoundsCount(parseInt(e.target.value))}
            disabled={loading}
            className="form-select"
            required
          >
            {[1, 2, 3, 4, 5, 6].map(num => (
              <option key={num} value={num}>
                {num} {num === 1 ? 'runda' : num < 5 ? 'rundy' : 'rund'}
              </option>
            ))}
          </select>
        </div>
        
        <Button type="submit" disabled={loading || !team1Name.trim() || !team2Name.trim()}>
          Rozpocznij grę
        </Button>
      </form>

      {error && <ErrorMessage message={error} />}
    </div>
  )
}

export default NewGameScreen

