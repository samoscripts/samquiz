function Button({ children, onClick, disabled, type = 'button', className = '', ...props }) {
  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`btn ${className}`}
      {...props}
    >
      {children}
    </button>
  )
}

export default Button

