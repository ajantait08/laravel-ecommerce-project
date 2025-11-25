<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Website')</title>

    {{-- Tailwind / CSS --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons (FontAwesome CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100">

    {{-- Include Navbar here if you want globally --}}
    {{-- @include('components.navbar') --}}

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    {{-- @include('components.footer') --}}
</body>
</html>
