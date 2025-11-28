import { useState, useEffect } from 'react'
import { useFaceOff } from './useFaceOff'
import { checkIfTopAnswer, determineFaceOffWinner } from './faceOffLogic'
import {
  processCorrectAnswer,
  addStrike,
  processStealResult
} from './gamePlayLogic'

function App() {
  const [question, setQuestion] = useState('Największe miasta w polsce')
  const [answersCount, setAnswersCount] = useState(10)
  const [team1Name, setTeam1Name] = useState('Drużyna 1')
  const [team2Name, setTeam2Name] = useState('Drużyna 2')
  const [activeTeam, setActiveTeam] = useState(1) // 1 lub 2
  const [gamePhase, setGamePhase] = useState('faceOff') // 'faceOff', 'playing', 'steal'
  const [team1RoundPoints, setTeam1RoundPoints] = useState(0)
  const [team1TotalPoints, setTeam1TotalPoints] = useState(0)
  const [team1Strikes, setTeam1Strikes] = useState(0)
  const [team2RoundPoints, setTeam2RoundPoints] = useState(0)
  const [team2TotalPoints, setTeam2TotalPoints] = useState(0)
  const [team2Strikes, setTeam2Strikes] = useState(0)
  const [currentRound, setCurrentRound] = useState(1)
  const [loading, setLoading] = useState(false)
  const [verifying, setVerifying] = useState(false)
  const [error, setError] = useState(null)
  const [data, setData] = useState(null)
  const [revealedAnswers, setRevealedAnswers] = useState(new Set())
  const [answerInput, setAnswerInput] = useState('')
  
  // Face Off state
  const {
    faceOffTeam1Answer,
    faceOffTeam2Answer,
    faceOffTeam1Points,
    faceOffTeam2Points,
    faceOffWinner,
    setFaceOffTeam1Answer,
    setFaceOffTeam2Answer,
    setFaceOffTeam1Points,
    setFaceOffTeam2Points,
    setFaceOffWinner,
    resetFaceOff
  } = useFaceOff()
  const [roundCompleted, setRoundCompleted] = useState(false)
  const [finalRoundPoints, setFinalRoundPoints] = useState({ team1: 0, team2: 0 })

  // Reset odkrytych odpowiedzi przy nowym pytaniu
  useEffect(() => {
    if (data) {
      setRevealedAnswers(new Set())
      setAnswerInput('')
      setGamePhase('faceOff')
      setTeam1Strikes(0)
      setTeam2Strikes(0)
      setTeam1RoundPoints(0)
      setTeam2RoundPoints(0)
      resetFaceOff()
      setRoundCompleted(false)
    }
  }, [data])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)
    setData(null)
    setRevealedAnswers(new Set())
    setAnswerInput('')
    setGamePhase('faceOff')

    try {
      const response = await fetch('http://127.0.0.1:8000/api/family-feud/question/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          question: question.trim(),
          answersCount: answersCount
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()
      setData(result)
    } catch (err) {
      setError(err.message || 'Wystąpił błąd podczas pobierania danych')
    } finally {
      setLoading(false)
    }
  }

  const handleFaceOffAnswer = async (teamNumber) => {
    if (!answerInput.trim() || !data || !data.answers) {
      return
    }

    // Sprawdź czy drużyna już odpowiedziała
    if ((teamNumber === 1 && faceOffTeam1Answer !== null) || 
        (teamNumber === 2 && faceOffTeam2Answer !== null)) {
      return
    }

    setVerifying(true)
    setError(null)

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/family-feud/question/${data.id}/verify`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          answer: answerInput.trim(),
          answersCount: answersCount
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.isCorrect && result.matchedAnswer && result.matchedAnswer.text) {
        const points = result.matchedAnswer.points || 0
        
        // Sprawdź czy to jest pierwsza (najwyżej punktowana) odpowiedź
        const isTopAnswer = checkIfTopAnswer(result.matchedAnswer.text, data.answers)
        
        if (teamNumber === 1) {
          setFaceOffTeam1Answer(result.matchedAnswer.text)
          setFaceOffTeam1Points(points)
        } else {
          setFaceOffTeam2Answer(result.matchedAnswer.text)
          setFaceOffTeam2Points(points)
        }

        // Dodaj odpowiedź do odkrytych
        setRevealedAnswers(prev => new Set([...prev, result.matchedAnswer.text]))
        setAnswerInput('')

        // Jeśli pierwsza drużyna podała najwyżej punktowaną odpowiedź - automatycznie wygrywa
        if (isTopAnswer && teamNumber === 1) {
          setFaceOffWinner(1)
          setActiveTeam(1)
          setGamePhase('playing')
          return
        }
        
        // Jeśli druga drużyna podała najwyżej punktowaną odpowiedź - automatycznie wygrywa
        if (isTopAnswer && teamNumber === 2) {
          setFaceOffWinner(2)
          setActiveTeam(2)
          setGamePhase('playing')
          return
        }

        // Sprawdź czy obie drużyny odpowiedziały - użyj setTimeout aby stan się zaktualizował
        setTimeout(() => {
          const winner = determineFaceOffWinner(
            teamNumber,
            points,
            faceOffTeam1Answer,
            faceOffTeam1Points,
            faceOffTeam2Answer,
            faceOffTeam2Points
          )
          
          if (winner !== null) {
            setFaceOffWinner(winner)
            setActiveTeam(winner)
            setGamePhase('playing')
          }
        }, 100)
      } else {
        // Błędna odpowiedź w Face Off - drużyna traci szansę
        if (teamNumber === 1) {
          setFaceOffTeam1Answer('')
        } else {
          setFaceOffTeam2Answer('')
        }
        setAnswerInput('')
      }
    } catch (err) {
      setError(err.message || 'Wystąpił błąd podczas weryfikacji odpowiedzi')
    } finally {
      setVerifying(false)
    }
  }

  const handleAnswerSubmit = async (e) => {
    e.preventDefault()
    
    if (!answerInput.trim() || !data || !data.answers) {
      return
    }

    // Jeśli jesteśmy w fazie Face Off, użyj specjalnej funkcji
    if (gamePhase === 'faceOff') {
      // W Face Off trzeba wskazać drużynę ręcznie - użyjemy activeTeam
      await handleFaceOffAnswer(activeTeam)
      return
    }

    // Jeśli jesteśmy w fazie steal
    if (gamePhase === 'steal') {
      await handleStealAnswer()
      return
    }

    // Normalna gra
    setVerifying(true)
    setError(null)

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/family-feud/question/${data.id}/verify`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          answer: answerInput.trim(),
          answersCount: answersCount
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.isCorrect && result.matchedAnswer && result.matchedAnswer.text) {
        // Przetwórz poprawną odpowiedź
        const points = result.matchedAnswer.points || 0
        const gameResult = processCorrectAnswer(
          result.matchedAnswer.text,
          points,
          activeTeam,
          revealedAnswers,
          team1RoundPoints,
          team2RoundPoints,
          data.answers.length
        )
        
        // Zaktualizuj stan
        setRevealedAnswers(gameResult.revealedAnswers)
        setTeam1RoundPoints(gameResult.team1RoundPoints)
        setTeam2RoundPoints(gameResult.team2RoundPoints)
        setAnswerInput('')
        
        // Sprawdź czy wszystkie odpowiedzi zostały odkryte
        if (gameResult.allAnswersRevealed) {
          setTimeout(() => {
            setFinalRoundPoints(gameResult.finalRoundPoints)
            setTeam1TotalPoints(prev => prev + gameResult.team1RoundPoints)
            setTeam2TotalPoints(prev => prev + gameResult.team2RoundPoints)
            setRoundCompleted(true)
            setGamePhase('completed')
          }, 500)
        }
      } else {
        // Błędna odpowiedź - dodaj strike
        const strikeResult = addStrike(activeTeam, team1Strikes, team2Strikes)
        setTeam1Strikes(strikeResult.team1Strikes)
        setTeam2Strikes(strikeResult.team2Strikes)
        
        if (strikeResult.shouldSwitchToSteal) {
          setTimeout(() => {
            setGamePhase('steal')
            setActiveTeam(strikeResult.nextActiveTeam)
          }, 500)
        }
        setAnswerInput('')
      }
    } catch (err) {
      setError(err.message || 'Wystąpił błąd podczas weryfikacji odpowiedzi')
    } finally {
      setVerifying(false)
    }
  }

  const handleStealAnswer = async () => {
    if (!answerInput.trim() || !data || !data.answers) {
      return
    }

    setVerifying(true)
    setError(null)

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/family-feud/question/${data.id}/verify`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          answer: answerInput.trim(),
          answersCount: answersCount
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.isCorrect && result.matchedAnswer && result.matchedAnswer.text) {
        // Przeciwnicy trafili - kradną wszystkie punkty
        const stealResult = processStealResult(
          true,
          activeTeam,
          team1RoundPoints,
          team2RoundPoints
        )
        
        setTeam1RoundPoints(stealResult.team1RoundPoints)
        setTeam2RoundPoints(stealResult.team2RoundPoints)
        setRevealedAnswers(prev => new Set([...prev, result.matchedAnswer.text]))
        
        setTimeout(() => {
          setFinalRoundPoints(stealResult.finalRoundPoints)
          setTeam1TotalPoints(prev => prev + stealResult.finalRoundPoints.team1)
          setTeam2TotalPoints(prev => prev + stealResult.finalRoundPoints.team2)
          setRoundCompleted(true)
          setGamePhase('completed')
        }, 1000)
      } else {
        // Przeciwnicy nie trafili - punkty zostają u pierwotnej drużyny
        const stealResult = processStealResult(
          false,
          activeTeam,
          team1RoundPoints,
          team2RoundPoints
        )
        
        setTimeout(() => {
          setFinalRoundPoints(stealResult.finalRoundPoints)
          setTeam1TotalPoints(prev => prev + stealResult.finalRoundPoints.team1)
          setTeam2TotalPoints(prev => prev + stealResult.finalRoundPoints.team2)
          setRoundCompleted(true)
          setGamePhase('completed')
        }, 1000)
      }
      setAnswerInput('')
    } catch (err) {
      setError(err.message || 'Wystąpił błąd podczas weryfikacji odpowiedzi')
    } finally {
      setVerifying(false)
    }
  }

  const handleNextRound = () => {
    setTeam1RoundPoints(0)
    setTeam2RoundPoints(0)
    setTeam1Strikes(0)
    setTeam2Strikes(0)
    setCurrentRound(prev => prev + 1)
    setRevealedAnswers(new Set())
    setAnswerInput('')
    setActiveTeam(1)
    setGamePhase('faceOff')
    setFaceOffTeam1Answer(null)
    setFaceOffTeam2Answer(null)
    setFaceOffTeam1Points(0)
    setFaceOffTeam2Points(0)
    setFaceOffWinner(null)
    setRoundCompleted(false)
    setFinalRoundPoints({ team1: 0, team2: 0 })
  }

  const handleEndRound = () => {
    setTeam1TotalPoints(prev => prev + team1RoundPoints)
    setTeam2TotalPoints(prev => prev + team2RoundPoints)
    setTeam1RoundPoints(0)
    setTeam2RoundPoints(0)
    setTeam1Strikes(0)
    setTeam2Strikes(0)
    setCurrentRound(prev => prev + 1)
    setRevealedAnswers(new Set())
    setAnswerInput('')
    setData(null)
    setActiveTeam(1)
    setGamePhase('faceOff')
    setFaceOffTeam1Answer(null)
    setFaceOffTeam2Answer(null)
    setFaceOffTeam1Points(0)
    setFaceOffTeam2Points(0)
    setFaceOffWinner(null)
    setRoundCompleted(false)
  }

  const handleNewGame = () => {
    setTeam1RoundPoints(0)
    setTeam1TotalPoints(0)
    setTeam1Strikes(0)
    setTeam2RoundPoints(0)
    setTeam2TotalPoints(0)
    setTeam2Strikes(0)
    setCurrentRound(1)
    setRevealedAnswers(new Set())
    setAnswerInput('')
    setData(null)
    setActiveTeam(1)
    setGamePhase('faceOff')
    setFaceOffTeam1Answer(null)
    setFaceOffTeam2Answer(null)
    setFaceOffTeam1Points(0)
    setFaceOffTeam2Points(0)
    setFaceOffWinner(null)
    setRoundCompleted(false)
    setFinalRoundPoints({ team1: 0, team2: 0 })
  }

  return (
    <>
      {/* Sekcja z odpowiedziami */}
      {data && data.answers && (
        <div className="container answers-container">
          <div className="answers-section-top">
            {/* Treść pytania i nazwy drużyn */}
            <div className="question-header-row">
              <div className="question-text-center">{data.question}</div>
            </div>
            <div className="team-names-row">
              <div className="team-name-left">{team1Name}</div>
              <div className="round-points-total">{team1RoundPoints + team2RoundPoints}</div>
              <div className="team-name-right">{team2Name}</div>
            </div>
            
            {/* Główna sekcja z odpowiedziami i X-ami */}
            <div className="answers-strikes-row">
              {/* Strike indicators - po lewej stronie */}
              <div className="strikes-left">
                {[1, 2, 3].map((index) => (
                  <div 
                    key={index} 
                    className={`strike-x ${index <= team1Strikes ? 'active' : ''}`}
                  >
                    ✕
                  </div>
                ))}
              </div>
              
              {/* Lista odpowiedzi w środku */}
              <div className="answers-content">
                <div className="answers-list">
                {data.answers.length > 0 ? (
                  data.answers.map((answer, index) => {
                    const isRevealed = revealedAnswers.has(answer.text)
                    const isLast = index === data.answers.length - 1
                    const canReveal = roundCompleted && !isRevealed
                    return (
                      <div 
                        key={index} 
                        className={`answer-item ${isRevealed ? 'revealed' : 'hidden'} ${isLast ? 'last-answer' : ''} ${canReveal ? 'clickable-reveal' : ''}`}
                        onClick={canReveal ? () => {
                          // Odsłoń tylko tę odpowiedź bez dodawania punktów
                          setRevealedAnswers(prev => new Set([...prev, answer.text]))
                        } : undefined}
                      >
                        <span className="answer-number">{index + 1}</span>
                        {isRevealed ? (
                          <>
                            <span className="answer-text">{answer.text}</span>
                            <span className="answer-points">{answer.points}</span>
                          </>
                        ) : (
                          <span className="answer-text hidden-text">•••••••••••••••••••••••••••••••</span>
                        )}
                        {isLast && (
                          <>
                            <div className="round-points-left">{team1RoundPoints}</div>
                            <div className="round-points-right">{team2RoundPoints}</div>
                          </>
                        )}
                      </div>
                    )
                  })
                ) : (
                  <p>Brak odpowiedzi</p>
                )}
                </div>
                
                {roundCompleted && (
                  <div className="quiz-complete">
                    <div className="round-complete-message">
                      🎉 Runda zakończona!
                      <div className="round-scores">
                        <div className="round-score-item">
                          {team1Name}: {finalRoundPoints.team1} pkt
                        </div>
                        <div className="round-score-item">
                          {team2Name}: {finalRoundPoints.team2} pkt
                        </div>
                      </div>
                    </div>
                    <div className="round-complete-actions">
                      <p className="reveal-hint">
                        Kliknij na nieodkryte odpowiedzi, aby je odsłonić
                      </p>
                      <button onClick={handleNextRound} className="next-round-btn">
                        Przejdź do kolejnej rundy
                      </button>
                    </div>
                  </div>
                )}
                
                {/* Pole do wpisywania odpowiedzi */}
                {gamePhase === 'faceOff' && (
                  <div className="face-off-section">
                    <div className="face-off-info">
                      <h3>FACE OFF - Kto pierwszy naciśnie i poda lepszą odpowiedź?</h3>
                      <div className="face-off-buttons">
                        <button
                          type="button"
                          onClick={() => {
                            setActiveTeam(1)
                            handleFaceOffAnswer(1)
                          }}
                          className={`face-off-btn team1 ${activeTeam === 1 ? 'active' : ''}`}
                          disabled={verifying || faceOffTeam1Answer !== null || gamePhase !== 'faceOff'}
                        >
                          {team1Name}
                          {faceOffTeam1Answer && (
                            <span className="face-off-result">
                              {faceOffTeam1Answer} ({faceOffTeam1Points} pkt)
                            </span>
                          )}
                        </button>
                        <button
                          type="button"
                          onClick={() => {
                            setActiveTeam(2)
                            handleFaceOffAnswer(2)
                          }}
                          className={`face-off-btn team2 ${activeTeam === 2 ? 'active' : ''}`}
                          disabled={verifying || faceOffTeam2Answer !== null || gamePhase !== 'faceOff'}
                        >
                          {team2Name}
                          {faceOffTeam2Answer && (
                            <span className="face-off-result">
                              {faceOffTeam2Answer} ({faceOffTeam2Points} pkt)
                            </span>
                          )}
                        </button>
                      </div>
                      {faceOffWinner && (
                        <div className="face-off-winner">
                          {faceOffWinner === 1 ? team1Name : team2Name} wygrywa Face Off!
                        </div>
                      )}
                    </div>
                    <form onSubmit={(e) => { e.preventDefault(); handleFaceOffAnswer(activeTeam) }} className="answer-input-form">
                      <input
                        id="answer"
                        value={answerInput}
                        onChange={(e) => setAnswerInput(e.target.value.toUpperCase())}
                        placeholder="WPISZ ODPOWIEDŹ..."
                        autoComplete="off"
                        className="answer-input-field"
                        disabled={verifying || !data}
                      />
                    </form>
                  </div>
                )}
                
                {!roundCompleted && gamePhase === 'steal' && (
                  <div className="steal-section">
                    <div className="steal-info">
                      <h3>
                        {activeTeam === 1 ? team1Name : team2Name} ma szansę ukraść punkty!
                        <br />
                        ({(activeTeam === 1 ? team2RoundPoints : team1RoundPoints)} pkt do kradzieży)
                      </h3>
                      <p>Jedna odpowiedź - jeśli trafisz, kradniesz wszystkie punkty!</p>
                    </div>
                    <form onSubmit={handleAnswerSubmit} className="answer-input-form">
                      <input
                        id="answer"
                        value={answerInput}
                        onChange={(e) => setAnswerInput(e.target.value.toUpperCase())}
                        placeholder="WPISZ ODPOWIEDŹ..."
                        autoComplete="off"
                        className="answer-input-field"
                        disabled={verifying || !data}
                      />
                    </form>
                  </div>
                )}
                
                {!roundCompleted && gamePhase === 'playing' && (
                  <form onSubmit={handleAnswerSubmit} className="answer-input-form">
                    <div className="playing-info">
                      <p>Teraz gra: <strong>{activeTeam === 1 ? team1Name : team2Name}</strong></p>
                      <p>Błędy: {activeTeam === 1 ? team1Strikes : team2Strikes} / 3</p>
                    </div>
                    <input
                      id="answer"
                      value={answerInput}
                      onChange={(e) => setAnswerInput(e.target.value.toUpperCase())}
                      placeholder="WPISZ ODPOWIEDŹ..."
                      autoComplete="off"
                      className="answer-input-field"
                      disabled={verifying || !data}
                    />
                  </form>
                )}
              </div>
              
              {/* Strike indicators - po prawej stronie */}
              <div className="strikes-right">
                {[1, 2, 3].map((index) => (
                  <div 
                    key={index} 
                    className={`strike-x ${index <= team2Strikes ? 'active' : ''}`}
                  >
                    ✕
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
      
      {/* Sekcja główna z formularzem i panelami */}
      <div className="container">
        <h1>🎯 Quiz Familiada</h1>
        
        {/* Panel drużyn */}
      <div className="teams-panel">
        <div className={`team-card ${activeTeam === 1 ? 'active' : ''}`}>
          <div className="team-header">
            <input
              type="text"
              value={team1Name}
              onChange={(e) => setTeam1Name(e.target.value)}
              className="team-name-input"
              placeholder="Drużyna 1"
            />
            <span className="team-badge">1</span>
          </div>
          <div className="team-scores">
            <div className="score-item">
              <span className="score-label">Runda:</span>
              <span className="score-value">{team1RoundPoints} pkt</span>
            </div>
            <div className="score-item">
              <span className="score-label">Razem:</span>
              <span className="score-value total">{team1TotalPoints} pkt</span>
            </div>
          </div>
        </div>

        <div className="round-info">
          <div className="round-number">Runda {currentRound}</div>
          <button onClick={handleEndRound} className="end-round-btn" disabled={!data}>
            Zakończ rundę
          </button>
          <button onClick={handleNewGame} className="new-game-btn">
            Nowa gra
          </button>
        </div>

        <div className={`team-card ${activeTeam === 2 ? 'active' : ''}`}>
          <div className="team-header">
            <input
              type="text"
              value={team2Name}
              onChange={(e) => setTeam2Name(e.target.value)}
              className="team-name-input"
              placeholder="Drużyna 2"
            />
            <span className="team-badge">2</span>
          </div>
          <div className="team-scores">
            <div className="score-item">
              <span className="score-label">Runda:</span>
              <span className="score-value">{team2RoundPoints} pkt</span>
            </div>
            <div className="score-item">
              <span className="score-label">Razem:</span>
              <span className="score-value total">{team2TotalPoints} pkt</span>
            </div>
          </div>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="question-form">
        <div className="form-row">
          <div className="form-group form-group-flex">
            <label htmlFor="question">Wpisz pytanie:</label>
            <input
              type="text"
              id="question"
              value={question}
              onChange={(e) => setQuestion(e.target.value)}
              placeholder="np. Największe miasta w polsce"
              disabled={loading}
            />
          </div>
          
          <div className="form-group form-group-flex">
            <label htmlFor="answersCount">Liczba odpowiedzi:</label>
            <select
              id="answersCount"
              value={answersCount}
              onChange={(e) => setAnswersCount(parseInt(e.target.value))}
              disabled={loading}
            >
              {[3, 4, 5, 6, 7, 8, 9, 10].map(num => (
                <option key={num} value={num}>{num}</option>
              ))}
            </select>
          </div>
        </div>
        
        <button type="submit" disabled={loading || !question.trim()}>
          {loading ? 'Generowanie...' : 'Generuj Pytanie'}
        </button>
      </form>

      {loading && <div className="loading">⏳ Generowanie odpowiedzi...</div>}

      {error && (
        <div className="error">
          <strong>Błąd:</strong> {error}
        </div>
      )}
      </div>
    </>
  )
}

export default App


