<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="robots" content="index,follow">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="copyright" content="© {{ date('Y') }} - Prefeitura de Umuarama" />
    <meta name="author" content="Divisão de Análise e Desenvolvimento de Sistemas | Prefeitura de Umuarama">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <meta property="article:author" content="Divisão de Análise e Desenvolvimento de Sistemas | Prefeitura de Umuarama">

    <title>{{ config('app.name', 'Prefeitura de Umuarama') }}</title>

    <link rel="stylesheet" href="/vendor/font-awesome/css/all.min.css" type="text/css" />
    <link rel="stylesheet" href="/vendor/adminlte/dist/css/adminlte.css" type="text/css" />
    <link rel="stylesheet" href="/vendor/bootstrap/dist/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/vendor/custom/css/main.css" />
    <link rel="shortcut icon" href="/favicons/favicon.png" type="image/x-icon" />

    <!-- Scripts -->
    <script type="text/javascript" src="/vendor/jquery/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-body-secondary">
    <div id="conteudo">
        <main>
            @yield('content')
            @yield('components.footer')
        </main>
    </div>
    <script type="text/javascript" src="/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="/vendor/adminlte/dist/js/adminlte.min.js"></script>
</body>

</html>