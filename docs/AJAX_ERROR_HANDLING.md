# Obsługa błędów AJAX w Symfony

## Backend (Symfony)

Listener `ApiExceptionListener` automatycznie przechwytuje wszystkie wyjątki dla żądań AJAX i zwraca je w formacie JSON:

```json
{
  "error": "komunikat błędu"
}
```

### Warunki działania listenera:

1. Żądanie musi mieć nagłówek `X-Requested-With: XMLHttpRequest` LUB
2. Żądanie musi mieć nagłówek `Accept: application/json` LUB
3. Ścieżka musi zaczynać się od `/api`

### Przykłady błędów:

- **404 Not Found**: `{ "error": "Game not found" }`
- **500 Internal Server Error**: `{ "error": "Wystąpił błąd serwera..." }` (w produkcji)
- **400 Bad Request**: `{ "error": "Invalid request data" }`

## Frontend - React (Fetch API)

### Przykład obsługi błędów w React:

```javascript
// services/api.js
async createGame(team1Name, team2Name) {
  const response = await fetch(`${API_BASE}/game/create`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      team1: team1Name,
      team2: team2Name,
    }),
  })

  if (!response.ok) {
    // Backend zwraca JSON z błędem
    const errorData = await response.json().catch(() => ({ error: 'Unknown error' }))
    throw new Error(errorData.error || `HTTP error! status: ${response.status}`)
  }

  return response.json()
}

// W komponencie React
try {
  const gameData = await gameApi.createGame(team1Name, team2Name)
  // Sukces
} catch (error) {
  // error.message zawiera komunikat z backendu
  setError(error.message)
}
```

## Frontend - jQuery AJAX

### Przykład obsługi błędów w jQuery:

```javascript
$.ajax({
  url: '/api/family-feud/game/create',
  method: 'POST',
  contentType: 'application/json',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
  },
  data: JSON.stringify({
    team1: 'Drużyna 1',
    team2: 'Drużyna 2'
  }),
  dataType: 'json',
  success: function(data) {
    // Obsługa sukcesu
    console.log('Gra utworzona:', data);
  },
  error: function(xhr, status, error) {
    let errorMessage = 'Wystąpił błąd';
    
    // Próbujemy sparsować odpowiedź JSON z błędem
    try {
      const errorData = JSON.parse(xhr.responseText);
      if (errorData.error) {
        errorMessage = errorData.error;
      }
    } catch (e) {
      // Jeśli nie udało się sparsować, używamy domyślnego komunikatu
      errorMessage = xhr.statusText || 'Nieznany błąd';
    }
    
    // Wstawiamy komunikat błędu do div#error
    $('#error').text(errorMessage).show();
  }
});
```

### Alternatywny sposób z `.done()` i `.fail()`:

```javascript
$.ajax({
  url: '/api/family-feud/game/create',
  method: 'POST',
  contentType: 'application/json',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
  },
  data: JSON.stringify({
    team1: 'Drużyna 1',
    team2: 'Drużyna 2'
  }),
  dataType: 'json'
})
.done(function(data) {
  // Sukces
  console.log('Gra utworzona:', data);
})
.fail(function(xhr) {
  // Błąd
  let errorMessage = 'Wystąpił błąd';
  
  try {
    const errorData = JSON.parse(xhr.responseText);
    errorMessage = errorData.error || errorMessage;
  } catch (e) {
    errorMessage = xhr.statusText || 'Nieznany błąd';
  }
  
  $('#error').text(errorMessage).show();
});
```

### Najprostszy przykład z użyciem `.text()`:

```javascript
$.ajax({
  url: '/api/family-feud/game/create',
  method: 'POST',
  contentType: 'application/json',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json'
  },
  data: JSON.stringify({
    team1: 'Drużyna 1',
    team2: 'Drużyna 2'
  }),
  dataType: 'json',
  success: function(data) {
    console.log('Sukces:', data);
  },
  error: function(xhr) {
    // Parsujemy JSON i wyświetlamy komunikat
    const errorData = JSON.parse(xhr.responseText || '{}');
    $('#error').text(errorData.error || 'Wystąpił błąd');
  }
});
```

## HTML przykład:

```html
<div id="error" style="display: none; color: red; padding: 10px; background: #fee; border: 1px solid #c33;"></div>
```

## Uwagi:

1. **Nagłówki AJAX**: Ważne jest wysłanie nagłówka `X-Requested-With: XMLHttpRequest` lub `Accept: application/json`, aby Symfony rozpoznało żądanie jako AJAX.

2. **Parsowanie błędów**: Zawsze próbuj sparsować odpowiedź jako JSON, nawet przy błędach, bo Symfony zwraca JSON dla żądań AJAX.

3. **Fallback**: Jeśli parsowanie się nie powiedzie, użyj domyślnego komunikatu błędu.

4. **Tryb debug**: W trybie deweloperskim (`APP_ENV=dev`) Symfony zwraca szczegółowe komunikaty błędów. W produkcji (`APP_ENV=prod`) komunikaty są ogólne dla błędów 500+.

