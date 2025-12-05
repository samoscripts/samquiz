import useGameStore from '../store/gameStore'
import { GAME_PHASES } from '../utils/gamePhases'
import NewGameScreen from '../components/game/Screens/NewGameScreen'
import NewRoundScreen from '../components/game/Screens/NewRoundScreen'
import FaceOffScreen from '../components/game/Screens/FaceOffScreen'
import PlayingScreen from '../components/game/Screens/PlayingScreen'
import StealScreen from '../components/game/Screens/StealScreen'
import EndRoundScreen from '../components/game/Screens/EndRoundScreen'
import PageContainer from '../components/layout/PageContainer'

function App() {
  const phase = useGameStore(state => state.game?.phase)

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
    </PageContainer>
  )
}

export default App

