function StrikeIndicator({ strikes = 0 }) {
  return (
    <div className="strike-indicator">
      {[1, 2, 3].map((index) => (
        <div 
          key={index} 
          className={`strike-x ${index <= strikes ? 'active' : ''}`}
        >
          ✕
        </div>
      ))}
    </div>
  )
}

export default StrikeIndicator

