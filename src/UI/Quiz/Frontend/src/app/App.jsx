import { useEffect, useRef, useState } from 'react'
import useGameStore from '../store/gameStore'
import { GAME_PHASES } from '../utils/gamePhases'
import NewGameScreen from '../components/game/Screens/NewGameScreen'
import NewRoundScreen from '../components/game/Screens/NewRoundScreen'
import FaceOffScreen from '../components/game/Screens/FaceOffScreen'
import PlayingScreen from '../components/game/Screens/PlayingScreen'
import StealScreen from '../components/game/Screens/StealScreen'
import EndRoundScreen from '../components/game/Screens/EndRoundScreen'
import PageContainer from '../components/layout/PageContainer'
import ErrorXOverlay from '../components/game/ErrorXOverlay'
import correctSound from '../sounds/correct5.wav'

function App() {
  const phase = useGameStore(state => state.game?.phase)
  const gameAlert = useGameStore(state => state.game?.gameAlert)
  const [showErrorX, setShowErrorX] = useState(false)
  const audioRef = useRef(null)
  const previousAlertRef = useRef(null)

  // Globalna obsługa alertów - działa niezależnie od aktualnego ekranu
  useEffect(() => {
    // Obsługuj alert tylko jeśli się zmienił
    if (gameAlert && gameAlert !== previousAlertRef.current) {
      previousAlertRef.current = gameAlert
      
      // Debug: loguj co przychodzi z backendu
      console.log('App: gameAlert received:', gameAlert)
      console.log('App: gameAlert.type:', gameAlert.type, typeof gameAlert.type)

      // Enum może być serializowany jako obiekt {value: "ERROR_X"} lub jako string "ERROR_X"
      const alertType = gameAlert.type?.value || gameAlert.type

      if (alertType === 'ERROR_X') {
        // Pokaż czerwony X
        console.log('App: Setting showErrorX to true')
        setShowErrorX(true)
      } else if (alertType === 'CORRECT_SOUND') {
        // Odtwórz dźwięk poprawnej odpowiedzi
        console.log('App: Playing correct sound')
        if (audioRef.current) {
          audioRef.current.currentTime = 0
          audioRef.current.play().catch(err => {
            console.warn('Nie udało się odtworzyć dźwięku:', err)
          })
        }
      }
    }
  }, [gameAlert])

  const renderScreen = () => {
    switch (phase) {
      case GAME_PHASES.NEW_GAME:
        return <NewGameScreen />
      
      case GAME_PHASES.NEW_ROUND:
        return <NewRoundScreen />
      
      case GAME_PHASES.FACE_OFF:
        return <FaceOffScreen />
      
      case GAME_PHASES.PLAYING:
        return <PlayingScreen />
      
      case GAME_PHASES.STEAL:
        return <StealScreen />
      
      case GAME_PHASES.END_ROUND:
        return <EndRoundScreen />
      
      case GAME_PHASES.END_GAME:
        return <EndRoundScreen /> // Możesz stworzyć osobny komponent dla końca gry
      
      default:
        return <NewGameScreen />
    }
  }

  return (
    <PageContainer>
      {renderScreen()}
      <ErrorXOverlay show={showErrorX} onHide={() => setShowErrorX(false)} />
      <audio ref={audioRef} src={correctSound} preload="auto" />
    </PageContainer>
  )
}

export default App

