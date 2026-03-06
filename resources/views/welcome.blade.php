<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Armutop</title>
        <style>
            :root { color-scheme: light; }
            body {
                margin: 0;
                font-family: "Instrument Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
                background: #0f1115;
                color: #111;
            }
            .page {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
                position: relative;
                overflow: hidden;
            }
            .page::before {
                content: "";
                position: absolute;
                inset: -20%;
                background-image: url("/inicio.jpg");
                background-size: cover;
                background-position: center;
                filter: blur(10px);
                transform: scale(1.1);
                animation: welcome-float 18s ease-in-out infinite alternate;
            }
            .page::after {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(120deg, rgba(15,17,21,0.55), rgba(15,17,21,0.25));
            }
            .card {
                width: 100%;
                max-width: 420px;
                background: rgba(255, 255, 255, 0.85);
                border: 1px solid rgba(255, 255, 255, 0.6);
                border-radius: 16px;
                padding: 28px;
                box-shadow: 0 6px 24px rgba(0,0,0,0.06);
                text-align: center;
                position: relative;
                z-index: 1;
                backdrop-filter: blur(4px);
            }
            .title {
                margin: 0 0 6px 0;
                font-size: 24px;
                font-weight: 600;
            }
            .subtitle {
                margin: 0 0 20px 0;
                font-size: 14px;
                color: #555;
            }
            .actions {
                display: grid;
                gap: 10px;
            }
            .btn {
                display: inline-flex;
                justify-content: center;
                align-items: center;
                gap: 8px;
                padding: 10px 14px;
                border-radius: 10px;
                border: 1px solid #111;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
            }
            .btn.primary {
                background: #111;
                color: #fff;
            }
            .btn.ghost {
                background: #fff;
                color: #111;
            }
            @keyframes welcome-float {
                0% { transform: scale(1.08) translate3d(-1%, -1%, 0); }
                100% { transform: scale(1.12) translate3d(1%, 1%, 0); }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="card">
                <h1 class="title">Armutop</h1>
                <p class="subtitle">Inicia sesion o crea una cuenta.</p>

                <div class="actions">
                    @if (Route::has('login'))
                        <a class="btn primary" href="{{ route('login') }}">Iniciar sesion</a>
                    @endif

                    @if (Route::has('register'))
                        <a class="btn ghost" href="{{ route('register') }}">Registrarse</a>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>
