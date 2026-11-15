<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>File too large</title>
  <style>
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f5f4; font-family: system-ui, -apple-system, sans-serif; color: #1c1917; padding: 16px; box-sizing: border-box; }
    .card { max-width: 440px; background: #fff; border-radius: 20px; padding: 28px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    h1 { font-size: 18px; margin: 0 0 8px; }
    p { font-size: 14px; line-height: 1.55; color: #57534e; margin: 0 0 20px; }
    a { display: inline-block; background: #1c1917; color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 10px 18px; border-radius: 999px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>File too large</h1>
    <p>{{ $message }} Nothing was saved.</p>
    <a href="javascript:history.back()">Go back</a>
  </div>
</body>
</html>
