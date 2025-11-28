/**
 * Logika rozgrywki tablicy odpowiedzi
 */

/**
 * Sprawdza czy wszystkie odpowiedzi zostały odkryte
 */
export function checkIfAllAnswersRevealed(revealedAnswers, totalAnswers) {
  return revealedAnswers.size === totalAnswers
}

/**
 * Dodaje punkty do odpowiedniej drużyny
 */
export function addPointsToTeam(activeTeam, points, team1RoundPoints, team2RoundPoints) {
  if (activeTeam === 1) {
    return {
      team1RoundPoints: team1RoundPoints + points,
      team2RoundPoints: team2RoundPoints
    }
  } else {
    return {
      team1RoundPoints: team1RoundPoints,
      team2RoundPoints: team2RoundPoints + points
    }
  }
}

/**
 * Dodaje strike do drużyny i sprawdza czy osiągnęła limit 3
 */
export function addStrike(activeTeam, team1Strikes, team2Strikes) {
  if (activeTeam === 1) {
    const newStrikes = team1Strikes + 1
    return {
      team1Strikes: newStrikes,
      team2Strikes: team2Strikes,
      shouldSwitchToSteal: newStrikes >= 3,
      nextActiveTeam: newStrikes >= 3 ? 2 : null
    }
  } else {
    const newStrikes = team2Strikes + 1
    return {
      team1Strikes: team1Strikes,
      team2Strikes: newStrikes,
      shouldSwitchToSteal: newStrikes >= 3,
      nextActiveTeam: newStrikes >= 3 ? 1 : null
    }
  }
}

/**
 * Oblicza punkty do kradzieży w fazie steal
 */
export function calculateStealPoints(activeTeam, team1RoundPoints, team2RoundPoints) {
  return activeTeam === 1 ? team2RoundPoints : team1RoundPoints
}

/**
 * Przetwarza wynik kradzieży punktów
 */
export function processStealResult(isCorrect, activeTeam, team1RoundPoints, team2RoundPoints) {
  if (isCorrect) {
    // Przeciwnicy trafili - kradną wszystkie punkty
    const stolenPoints = calculateStealPoints(activeTeam, team1RoundPoints, team2RoundPoints)
    
    return {
      team1RoundPoints: activeTeam === 1 ? team1RoundPoints + stolenPoints : 0,
      team2RoundPoints: activeTeam === 2 ? team2RoundPoints + stolenPoints : 0,
      finalRoundPoints: {
        team1: activeTeam === 1 ? stolenPoints : 0,
        team2: activeTeam === 2 ? stolenPoints : 0
      }
    }
  } else {
    // Przeciwnicy nie trafili - punkty zostają u pierwotnej drużyny
    return {
      team1RoundPoints: team1RoundPoints,
      team2RoundPoints: team2RoundPoints,
      finalRoundPoints: {
        team1: team1RoundPoints,
        team2: team2RoundPoints
      }
    }
  }
}

/**
 * Przetwarza poprawną odpowiedź podczas normalnej gry
 */
export function processCorrectAnswer(
  matchedAnswerText,
  points,
  activeTeam,
  revealedAnswers,
  team1RoundPoints,
  team2RoundPoints,
  totalAnswers
) {
  // Sprawdź czy odpowiedź już była odkryta
  const alreadyRevealed = revealedAnswers.has(matchedAnswerText)
  const newRevealed = new Set(revealedAnswers)
  
  // Dodaj odpowiedź tylko jeśli nie była już odkryta
  if (!alreadyRevealed) {
    newRevealed.add(matchedAnswerText)
  }
  
  // Dodaj punkty tylko jeśli odpowiedź nie była wcześniej odkryta
  const pointsUpdate = alreadyRevealed 
    ? { team1RoundPoints, team2RoundPoints }
    : addPointsToTeam(activeTeam, points, team1RoundPoints, team2RoundPoints)
  
  const allRevealed = checkIfAllAnswersRevealed(newRevealed, totalAnswers)
  
  return {
    revealedAnswers: newRevealed,
    ...pointsUpdate,
    allAnswersRevealed: allRevealed,
    finalRoundPoints: allRevealed ? {
      team1: pointsUpdate.team1RoundPoints,
      team2: pointsUpdate.team2RoundPoints
    } : null
  }
}

