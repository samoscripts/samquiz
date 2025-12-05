import { create } from 'zustand'

/**
 * Store przechowuje dokładnie to, co zwraca backend
 * Struktura 1:1 z backendem - bez mapowania
 */
const useGameStore = create((set) => ({
  // Stan gry z backendu - dokładnie jak w JSON (1:1)
  game: null, // { roundsCount, currentRound, gameId, phase, teamsCollection, question, answersCount }
  
  // UI state (tylko lokalne)
  loading: false,
  error: null,
  answerInput: '',
  
  // Akcje
  setGameState: (gameData) => {
    set({ game: gameData });
  },
  
  setAnswerInput: (value) => set({ answerInput: value }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),
  
  reset: () => set({
    game: null,
    answerInput: '',
    error: null,
    loading: false,
  }),
}))

export default useGameStore

