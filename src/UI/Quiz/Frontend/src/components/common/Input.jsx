function Input({ label, value, onChange, disabled, required, placeholder, type = 'text', className = '', ...props }) {
  return (
    <div className={`form-group ${className}`}>
      {label && <label htmlFor={props.id}>{label}</label>}
      <input
        type={type}
        value={value}
        onChange={onChange}
        disabled={disabled}
        required={required}
        placeholder={placeholder}
        className="form-input"
        {...props}
      />
    </div>
  )
}

export default Input

