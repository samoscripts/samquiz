import { useState, useEffect } from 'react'

function App() {
  const [question, setQuestion] = useState('Największe miasta w polsce')
  const [loading, setLoading] = useState(false)
  const [verifying, setVerifying] = useState(false)
  const [error, setError] = useState(null)
  const [data, setData] = useState(null)
  const [revealedAnswers, setRevealedAnswers] = useState(new Set())
  const [answerInput, setAnswerInput] = useState('')

  // Reset odkrytych odpowiedzi przy nowym pytaniu
  useEffect(() => {
    if (data) {
      setRevealedAnswers(new Set())
      setAnswerInput('')
    }
  }, [data])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)
    setData(null)
    setRevealedAnswers(new Set())
    setAnswerInput('')

    try {
      const response = await fetch('http://127.0.0.1:8000/api/family-feud/question/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ question: question.trim() }),
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

  const handleAnswerSubmit = async (e) => {
    e.preventDefault()
    
    if (!answerInput.trim() || !data || !data.answers) {
      return
    }

    setVerifying(true)
    setError(null)

    try {
      const response = await fetch('http://127.0.0.1:8000/api/family-feud/question/verify', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          answer: answerInput.trim(),
          answers: data.answers,
          questionId: data.id
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      if (result.isCorrect && result.matchedAnswer.text.length > 0) {
        // Dodaj nowe odkryte odpowiedzi
        setRevealedAnswers(prevRevealed => {
          const newRevealed = new Set(prevRevealed)
          if (!prevRevealed.has(result.matchedAnswer.text)) {
            newRevealed.add(result.matchedAnswer.text)
          }
          return newRevealed
        })
        setAnswerInput('') // Wyczyść input po znalezieniu odpowiedzi
      }
    } catch (err) {
      setError(err.message || 'Wystąpił błąd podczas weryfikacji odpowiedzi')
    } finally {
      setVerifying(false)
    }
  }

  return (
    <div className="container">
      <h1>🎯 Quiz Familiada</h1>
      
      <form onSubmit={handleSubmit} className="question-form">
        <div className="form-group">
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

      {data && (
        <div className="results">
          <div className="question-display">
            <h2>📝 Pytanie:</h2>
            <p>{data.question}</p>
          </div>
          
          <div className="answer-input-section">
            <form onSubmit={handleAnswerSubmit}>
              <div className="form-group">
                <label htmlFor="answer">Wpisz odpowiedź:</label>
                <input
                  type="text"
                  id="answer"
                  value={answerInput}
                  onChange={(e) => setAnswerInput(e.target.value)}
                  placeholder="Wpisz odpowiedź..."
                  autoComplete="off"
                />
              </div>
              <button 
                type="submit" 
                className="answer-submit-btn"
                disabled={verifying || !answerInput.trim()}
              >
                {verifying ? 'Sprawdzanie...' : 'Sprawdź'}
              </button>
            </form>
          </div>
          
          <h3>Odpowiedzi:</h3>
          <div className="answers-list">
            {data.answers && data.answers.length > 0 ? (
              data.answers.map((answer, index) => {
                const isRevealed = revealedAnswers.has(answer.text)
                return (
                  <div 
                    key={index} 
                    className={`answer-item ${isRevealed ? 'revealed' : 'hidden'}`}
                  >
                    {isRevealed ? (
                      <>
                        <span className="answer-text">{answer.text}</span>
                        <span className="answer-points">{answer.points} pkt</span>
                      </>
                    ) : (
                      <>
                        <span className="answer-text hidden-text">???</span>
                        <span className="answer-points hidden-points">? pkt</span>
                      </>
                    )}
                  </div>
                )
              })
            ) : (
              <p>Brak odpowiedzi</p>
            )}
          </div>
          
          {data.answers && revealedAnswers.size === data.answers.length && (
            <div className="quiz-complete">
              🎉 Gratulacje! Odkryłeś wszystkie odpowiedzi!
            </div>
          )}
        </div>
      )}
    </div>
  )
}

export default App


