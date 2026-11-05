<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu unavailable</title>
    <style>
        :root { --ink:#1a1814; --muted:#6b6560; --paper:#f7f3ec; --accent:#c45c26; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, #ffe8d6 0%, transparent 55%), var(--paper);
            color: var(--ink); padding: 24px; text-align: center;
        }
        h1 { font-size: 1.4rem; margin: 0 0 8px; }
        p { color: var(--muted); margin: 0; max-width: 28rem; }
    </style>
</head>
<body>
    <div>
        <h1>{{ $setting->name ?? 'Menu' }}</h1>
        <p>This digital menu is temporarily unavailable. Please ask staff for the latest menu.</p>
    </div>
</body>
</html>
