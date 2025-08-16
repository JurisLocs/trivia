<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Number Trivia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/game.css') }}">
</head>
<body>
  <h1>Number Trivia</h1>

  @if(($view ?? 'landing') === 'landing')
    <div class="card">
      <p class="muted">Answer up to 20 questions. One mistake ends the run. Questions are fetched from <code>numbersapi.com</code>. No repeats within a game.</p>
      <div class="spacer"></div>
      <form method="POST" action="{{ route('game.start') }}">
        @csrf
        <button class="btn primary">Start Game</button>
      </form>
    </div>
  @elseif(($view ?? 'play') === 'play')
    @php($g = $game ?? [])
    <div class="row" style="align-items:center; justify-content:space-between;">
      <div class="pill">Score: {{ $g['score'] ?? 0 }}</div>
      <div class="pill">Question: {{ ($g['count'] ?? 0) + 1 }} / {{ $g['limit'] ?? 20 }}</div>
    </div>
    <div class="spacer"></div>

    @if(!empty($g['error']))
      <div class="error">{{ $g['error'] }}</div>
      <div class="spacer"></div>
      <form method="GET" action="{{ route('game.index') }}">
        <button class="btn">Continue</button>
      </form>
    @elseif(empty($g['current']))
      <div class="card"><p>Loading question…</p></div>
    @else
      <div class="card">
        <p class="fs-115">
          {{ $g['current']['text'] }}
        </p>

        <div class="choices">
          @foreach($g['current']['choices'] as $opt)
            <form method="POST" action="{{ route('game.guess') }}">
              @csrf
              <input type="hidden" name="choice" value="{{ $opt }}">
              <button class="btn" type="submit">{{ $opt }}</button>
            </form>
          @endforeach
        </div>
      </div>
    @endif

    <div class="spacer"></div>
    <form method="POST" action="{{ route('game.restart') }}">
      @csrf
      <button class="btn">Restart</button>
    </form>

    @error('choice')
      <div class="spacer"></div>
      <div class="error">{{ $message }}</div>
    @enderror

  @elseif(($view ?? 'summary') === 'summary')
    @php($g = $game ?? [])
    <div class="card">
      <h2 class="m0-05">Game Over</h2>
      <p class="muted">Final score: <strong>{{ $g['score'] ?? 0 }}</strong> / {{ $g['limit'] ?? 20 }}</p>
      @if(!empty($g['last']))
        <hr/>
        <p class="mt-025"><strong>Last question:</strong></p>
        <p>{{ $g['last']['question'] }}</p>
        <p><strong>Correct answer:</strong> {{ $g['last']['answer'] }}</p>
        @if(isset($g['last']['picked']) && $g['last']['picked'] !== $g['last']['answer'])
          <p><strong>Your answer:</strong> {{ $g['last']['picked'] }}</p>
        @endif
        <details class="mt-05">
          <summary class="muted">Original fact</summary>
          <p class="muted mt-05">{{ $g['last']['source'] }}</p>
        </details>
      @endif
      <div class="spacer"></div>
      <form method="POST" action="{{ route('game.restart') }}">
        @csrf
        <button class="btn primary">Play Again</button>
      </form>
    </div>
  @endif
</body>
</html>
