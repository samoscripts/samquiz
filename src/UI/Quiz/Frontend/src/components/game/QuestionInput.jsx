import { forwardRef } from 'react'

const QuestionInput = forwardRef(function QuestionInput({ value, onChange, disabled, placeholder, onSubmit, ...props }, ref) {
  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && onSubmit && !disabled) {
      e.preventDefault()
      onSubmit(e)
    }
  }

  return (
    <input
      ref={ref}
      type="text"
      value={value}
      onChange={onChange}
      onKeyPress={handleKeyPress}
      disabled={disabled}
      placeholder={placeholder}
      className="question-input"
      autoComplete="off"
      {...props}
    />
  )
})


export default QuestionInput

