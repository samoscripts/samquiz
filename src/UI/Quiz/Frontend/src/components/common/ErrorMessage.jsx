function ErrorMessage({ message }) {
  if (!message) return null

  return (
    <div className="error-message">
      <strong>Błąd:</strong> {message}
    </div>
  )
}

export default ErrorMessage

