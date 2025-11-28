/**
 * Logika Face Off - porównywanie odpowiedzi i określanie zwycięzcy
 */

export function checkIfTopAnswer(matchedAnswerText, answers) {
  return answers.length > 0 && answers[0].text === matchedAnswerText
}

export function determineFaceOffWinner(teamNumber, points, faceOffTeam1Answer, faceOffTeam1Points, faceOffTeam2Answer, faceOffTeam2Points) {
  const team1Answered = teamNumber === 1 || faceOffTeam1Answer !== null
  const team2Answered = teamNumber === 2 || faceOffTeam2Answer !== null
  
  if (!team1Answered || !team2Answered) {
    return null
  }
  
  const team1Pts = teamNumber === 1 ? points : faceOffTeam1Points
  const team2Pts = teamNumber === 2 ? points : faceOffTeam2Points
  
  if (team1Pts > team2Pts) {
    return 1
  } else if (team2Pts > team1Pts) {
    return 2
  } else {
    // Remis - drużyna która odpowiedziała jako pierwsza wygrywa
    return faceOffTeam1Answer !== null ? 1 : 2
  }
}

