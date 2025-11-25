<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen flex justify-center items-center bg-gradient-to-r from-blue-100 to-blue-200">

    <div class="bg-white shadow-xl rounded-3xl p-8 w-full max-w-md">

        <div class="flex justify-center mb-6">
            <div class="bg-blue-500 p-4 rounded-full">
                <i class="fa-solid fa-user text-white text-3xl"></i>
            </div>
        </div>

        {{-- SUCCESS & ERROR MESSAGES --}}
        @if(session('success'))
            <p class="text-center text-green-600 text-sm mb-3">{{ session('success') }}</p>
        @endif

        @if(session('error'))
            <p class="text-center text-red-500 text-sm mb-3">{{ session('error') }}</p>
        @endif

        {{-- REGISTRATION FORM --}}
        <form method="POST" action="{{ route('register.submit') }}" class="space-y-4" autocomplete="off">
            @csrf

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-user text-gray-400 mr-2"></i>
                <input
                    type="text"
                    name="firstname"
                    placeholder="First Name"
                    value="{{ old('firstname') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('firstname')
                <p class="text-red-500 text-xs ml-3">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-user text-gray-400 mr-2"></i>
                <input
                    type="text"
                    name="lastname"
                    placeholder="Last Name"
                    value="{{ old('lastname') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('lastname')
                <p class="text-red-500 text-xs ml-3">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-envelope text-gray-400 mr-2"></i>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value="{{ old('email') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('email')
                <p class="text-red-500 text-xs ml-3">{{ $message }}</p>
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
                <p class="text-red-500 text-xs ml-3">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-lock text-gray-400 mr-2"></i>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm Password"
                    class="w-full outline-none"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-full font-semibold hover:bg-blue-700 transition"
            >
                REGISTER
            </button>

        </form>
    </div>

</body>
</html>
