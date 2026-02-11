<!DOCTYPE html>
<html lang="en">
    
<script>
    (() => {
      'use strict'
      const getStoredTheme = () => localStorage.getItem('theme')
      const setStoredTheme = theme => localStorage.setItem('theme', theme)

      const getPreferredTheme = () => {
        const storedTheme = getStoredTheme()
        if (storedTheme) {
          return storedTheme
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
      }

      const setTheme = theme => {
        if (theme === 'auto') {
          document.documentElement.setAttribute('data-bs-theme', (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'))
        } else {
          document.documentElement.setAttribute('data-bs-theme', theme)
        }
      }

      setTheme(getPreferredTheme())

      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const storedTheme = getStoredTheme()
        if (storedTheme !== 'light' && storedTheme !== 'dark') {
          setTheme(getPreferredTheme())
        }
      })
    })()
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharemeister - @yield('title')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { --sidebar-width: 240px; }
        body { background-color: #f8f9fa; font-family: 'Inter', -apple-system, sans-serif; }
        .navbar-brand { font-weight: 800; letter-spacing: -0.5px; }
        .card { border: none; transition: all 0.2s ease; }
        .footer { background: #1a1d20; }

        /* Falls du spezifische Farben für den Darkmode anpassen willst */
        [data-bs-theme="dark"] body {
            background-color: #121212; /* Etwas dunkler als der Standard-Grey */
        }

        /* Deine Karten-Styles brauchen evtl. Anpassung für Darkmode */
        [data-bs-theme="dark"] .card {
            background-color: #1e1e1e;
            border: 1px solid #333;
        }

    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-camera-fill me-2 text-primary"></i> Sharemeister
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('screenshot.list') }}">Screenshots</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('screenshot.upload') }}">Upload</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('account.settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        @yield('content')
    </main>

    <footer class="bg-body-tertiary text-body-secondary mt-auto py-4 border-top">
        <div class="container text-center">
            <small>Sharemeister v0.0.1 &bull; Built with Laravel &bull; <a href="https://github.com/flymia/Sharemeister/" class="text-white-50">GitHub</a></small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</body>
</html>