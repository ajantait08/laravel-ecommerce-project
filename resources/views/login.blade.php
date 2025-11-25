<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Tailwind CDN (or you can use Vite if already configured) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons (FontAwesome CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen flex justify-center items-center bg-gradient-to-r from-blue-100 to-blue-200">

    <div class="bg-white shadow-xl rounded-3xl p-8 w-full max-w-md">

        <div class="flex justify-center mb-6">
            <div class="bg-blue-500 p-4 rounded-full">
                <i class="fa-solid fa-user text-white text-3xl"></i>
            </div>
        </div>

        {{-- SUCCESS OR ERROR MESSAGE --}}
        @if(session('error'))
            <p class="text-center text-red-500 text-sm mb-3">{{ session('error') }}</p>
        @endif
        @if(session('success'))
            <p class="text-center text-green-600 text-sm mb-3">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4" autocomplete="off">
            @csrf

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-user text-gray-400 mr-2"></i>
                <input
                    type="email"
                    name="email"
                    placeholder="Username"
                    value="{{ old('email') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('email')
                <p class="text-red-500 text-xs ml-3 mt-1">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-lock text-gray-400 mr-2"></i>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('password')
                <p class="text-red-500 text-xs ml-3 mt-1">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-full font-semibold hover:bg-blue-700 transition"
            >
                LOGIN
            </button>

            <div class="flex items-center text-sm text-gray-500 mt-2">
                <label class="flex items-center gap-1 justify-between">
                    Don't Have an Account?
                </label>

                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">
                    &nbsp;&nbsp;Sign Up Now
                </a>
            </div>
        </form>

    </div>

</body>
</html>
