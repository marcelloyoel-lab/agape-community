<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />

    <title>Pemuda Agape</title>
    <meta name="theme-color" content="#712cf9" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body>

    @include('layouts.main.navbar')

    <div class="container-fluid">
        <div class="row">

            @include('layouts.main.sidebar')

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                @include('components.app.alert')
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>