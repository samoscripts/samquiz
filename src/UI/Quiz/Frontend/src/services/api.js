const API_BASE = '/api/family-feud'

/**
 * Serwis API - tylko wywołania HTTP
 * Zwraca dokładnie to, co backend
 */
export const gameApi = {
  /**
   * Tworzy nową grę
   * Backend zwraca: { gameId, teamsCollection: { teams: { "1": {...}, "2": {...} }, activeTeamKey }, phase, currentRound, roundsCount }
   */
  async createGame(team1Name, team2Name, roundsCount = 3) {
    const response = await fetch(`${API_BASE}/game/create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        team1: team1Name,
        team2: team2Name,
        roundsCount: roundsCount,
      }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }

    return response.json()
  },

  /**
   * Generuje pytanie dla nowej rundy
   * Backend zwraca pełny stan gry: { gameId, phase, teamsCollection, question: { text, answerCollection: { answers: [...] }, revealedAnswers: { answers: [...] } }, ... }
   */
  async createNewRound(gameId, questionText, answersCount = 10) {
    const response = await fetch(`${API_BASE}/game/${gameId}/newRound`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        question: questionText,
        answersCount: Math.max(3, Math.min(7, answersCount)),
      }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }

    return response.json()
  },

  /**
   * Weryfikuje odpowiedź
   * Backend zwraca pełny stan gry: { gameId, phase, teamsCollection, question: { text, answerCollection: { answers: [...] }, revealedAnswers: { answers: [...] } }, ... }
   */
  async verifyAnswer(gameId, answer) {
    const response = await fetch(`${API_BASE}/game/${gameId}/verifyAnswer`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        answer: answer.trim(),
      }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }

    return response.json()
  },

  async setActiveTeam(gameId, teamId) {
    const response = await fetch(`${API_BASE}/game/${gameId}/setActiveTeam`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        teamId: teamId,
      }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }
    return response.json()
  },

  /**
   * Przygotowuje następną rundę (zwiększa numer rundy, ustawia phase na NEW_ROUND)
   * Backend zwraca pełny stan gry: { gameId, phase: 'NEW_ROUND', currentRound: n+1, ... }
   */
  async nextRound(gameId) {
    const response = await fetch(`${API_BASE}/game/${gameId}/nextRound`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }
    return response.json()
  },

  /**
   * Odkrywa odpowiedź w fazie END_ROUND
   * Backend zwraca pełny stan gry z zaktualizowanym revealedAnswers
   */
  async revealAnswer(gameId, answerText) {
    const response = await fetch(`${API_BASE}/game/${gameId}/revealAnswer`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        answerText: answerText,
      }),
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Unknown error' }))
      throw new Error(error.error || `HTTP error! status: ${response.status}`)
    }
    return response.json()
  },
}

