<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.x/dist/tabler-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
<link rel="stylesheet" href="{{ asset('css/components/searchbar.css') }}">
</head>
<body>
@include('partials.nav')

    <main>
        @yield('content')
    </main>

@include('partials.footer')

@include('partials.modal')

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
