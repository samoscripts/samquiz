function TeamCard({ team, isActive, roundPoints = 0 }) {
  return (
    <div className={`team-card ${isActive ? 'active' : ''}`}>
      <div className="team-header">
        <div className="team-name">{team.name}</div>
        <div className="team-badge">{team === 'team1' ? '1' : '2'}</div>
      </div>
      <div className="team-scores">
        <div className="score-item">
          <span className="score-label">Runda:</span>
          <span className="score-value">{roundPoints} pkt</span>
        </div>
        <div className="score-item">
          <span className="score-label">Razem:</span>
          <span className="score-value total">{team.totalPoints || 0} pkt</span>
        </div>
      </div>
    </div>
  )
}

export default TeamCard

