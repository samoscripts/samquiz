import { useState } from 'react'

export function useFaceOff() {
  const [faceOffTeam1Answer, setFaceOffTeam1Answer] = useState(null)
  const [faceOffTeam2Answer, setFaceOffTeam2Answer] = useState(null)
  const [faceOffTeam1Points, setFaceOffTeam1Points] = useState(0)
  const [faceOffTeam2Points, setFaceOffTeam2Points] = useState(0)
  const [faceOffWinner, setFaceOffWinner] = useState(null)

  const resetFaceOff = () => {
    setFaceOffTeam1Answer(null)
    setFaceOffTeam2Answer(null)
    setFaceOffTeam1Points(0)
    setFaceOffTeam2Points(0)
    setFaceOffWinner(null)
  }

  return {
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
  }
}

