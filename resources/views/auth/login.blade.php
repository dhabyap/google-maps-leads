<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Radar Klien</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
      font-family: 'Inter', system-ui, sans-serif;
      background: #10121a; color: #e9e9ef;
    }
    .card {
      background: #1a1d29; border: 1px solid #2b3040; border-radius: 12px;
      padding: 32px; width: 320px;
    }
    h1 { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; margin: 0 0 4px; }
    h1 span { color: #e8a33d; }
    .sub { color: #8b8fa3; font-size: .82rem; margin-bottom: 22px; }
    input {
      width: 100%; padding: 11px 13px; border-radius: 8px; border: 1px solid #2b3040;
      background: #22263566; color: #e9e9ef; font-size: .9rem; margin-bottom: 12px;
    }
    button {
      width: 100%; padding: 11px; border: none; border-radius: 8px; cursor: pointer;
      background: #e8a33d; color: #10121a; font-weight: 600; font-size: .9rem;
    }
    button:hover { filter: brightness(1.06); }
    .err { color: #e2574c; font-size: .8rem; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Radar <span>Klien</span></h1>
    <div class="sub">Masuk untuk lihat prospek</div>

    @if($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login">
      @csrf
      <input type="password" name="password" placeholder="Password dashboard" autofocus>
      <button type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>
