function SingleStrikeIndicator({ strikes = 0 }) {
  // Wysokość jednego X to ~60px + 6px gap, więc 3 X-y to ~192px
  // Jeden wydłużony X powinien mieć taką samą wysokość
  return (
    <div className="single-strike-indicator">
      <div className={`strike-x single-strike-x ${strikes > 0 ? 'active' : ''}`}>
        ✕
      </div>
    </div>
  )
}

export default SingleStrikeIndicator

