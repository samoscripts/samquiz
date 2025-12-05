import { gameApi } from '../../../services/api'
import useGameStore from '../../../store/gameStore'
import AnswerBoard from '../AnswerBoard'
import ScoreBoard from '../ScoreBoard'
import Button from '../../common/Button'
import ErrorMessage from '../../common/ErrorMessage'

function EndRoundScreen() {
  const game = useGameStore(state => state.game)
  const { 
    setGameState,
    setLoading,
    setError,
    loading,
    error 
  } = useGameStore()
  
  const teamsCollection = game?.teamsCollection
  const question = game?.question
  const currentRound = game?.currentRound
  const roundsCount = game?.roundsCount
  const gameId = game?.gameId
  const roundPoints = game?.roundPoints
  const canStartNextRound = currentRound < roundsCount

  const handleRevealAnswer = async (answerText) => {
    if (!gameId || !answerText) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Backend dodaje odpowiedź do revealedAnswers
      const gameData = await gameApi.revealAnswer(gameId, answerText)
      setGameState(gameData)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleNextRound = async () => {
    if (!gameId || !canStartNextRound) {
      return
    }

    setLoading(true)
    setError(null)

    try {
      // Backend zwiększa numer rundy i ustawia phase na NEW_ROUND
      const gameData = await gameApi.nextRound(gameId)
      setGameState(gameData)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="end-round-screen">
      <h2>🎉 Runda {currentRound} zakończona!</h2>

      {question && (
        <div className="question-display">
          <h3>{question.text}</h3>
        </div>
      )}

      <ScoreBoard teamsCollection={teamsCollection} roundPoints={roundPoints} />

      <AnswerBoard 
        answers={question?.answerCollection?.answers || []}
        revealedAnswers={question?.revealedAnswers?.answers || []}
        onAnswerClick={handleRevealAnswer}
        disabled={loading}
      />

      <div className="end-round-controls">
        <div className="round-summary">
          <h3>Wyniki rundy:</h3>
          <div className="round-scores">
            <div className="round-score-item">
              {teamsCollection?.teams?.["1"]?.name || 'Drużyna 1'}: {teamsCollection?.teams?.["1"]?.totalPoints || 0} pkt
            </div>
            <div className="round-score-item">
              {teamsCollection?.teams?.["2"]?.name || 'Drużyna 2'}: {teamsCollection?.teams?.["2"]?.totalPoints || 0} pkt
            </div>
          </div>
        </div>

        {canStartNextRound ? (
          <Button 
            onClick={handleNextRound}
            disabled={loading}
          >
            Rozpocznij rundę {currentRound + 1}
          </Button>
        ) : (
          <div className="game-over">
            <h3>🎊 Gra zakończona!</h3>
            <p>Wszystkie rundy zostały rozegrane.</p>
          </div>
        )}
      </div>

      {error && <ErrorMessage message={error} />}
    </div>
  )
}

export default EndRoundScreen

