function ScoreBoard({ teamsCollection, roundPoints, activeTeamKey }) {
  const team1 = teamsCollection?.teams?.["1"] || { name: '', totalPoints: 0 }
  const team2 = teamsCollection?.teams?.["2"] || { name: '', totalPoints: 0 }
  const isTeam1Active = activeTeamKey === "1" || activeTeamKey === 1
  const isTeam2Active = activeTeamKey === "2" || activeTeamKey === 2

  return (
    <div className="score-board">
      <div className={`team-score ${isTeam1Active ? 'active' : 'inactive'}`}>
        <div className="team-name">{team1.name}</div>
        <div className="team-points">
          {roundPoints !== undefined && roundPoints !== null && (
            <div className="round-points">Runda: {roundPoints} pkt</div>
          )}
          <div className="total-points">Razem: {team1.totalPoints || 0} pkt</div>
        </div>
      </div>
      
      <div className="score-separator">VS</div>
      
      <div className={`team-score ${isTeam2Active ? 'active' : 'inactive'}`}>
        <div className="team-name">{team2.name}</div>
        <div className="team-points">
          {roundPoints !== undefined && roundPoints !== null && (
            <div className="round-points">Runda: {roundPoints} pkt</div>
          )}
          <div className="total-points">Razem: {team2.totalPoints || 0} pkt</div>
        </div>
      </div>
    </div>
  )
}

export default ScoreBoard

