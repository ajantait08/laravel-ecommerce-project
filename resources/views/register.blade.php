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
                <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
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
                <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
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
                <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-envelope text-gray-400 mr-2"></i>
                <input
                    type="text"
                    name="phone"
                    placeholder="Phone Number"
                    value="{{ old('phone') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('phone')
                <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-lock text-gray-400 mr-2"></i>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    value="{{ old('password') }}"
                    class="w-full outline-none"
                    required
                />
            </div>
            @error('password')
                <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
            @enderror

            <div class="flex items-center border rounded-full px-3 py-2">
                <i class="fa-solid fa-lock text-gray-400 mr-2"></i>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm Password"
                    value="{{ old('password_confirmation') }}"
                    class="w-full outline-none"
                    required
                />
            </div>

            @error('password_confirmation')
            <p class="text-red-500 text-xs ml-3 font-bold">{{ $message }}</p>
            @enderror

            <input type="hidden" name="from" value="{{ $from }}">

            <button
                type="submit"
                id="registerBtn"
                class="w-full bg-blue-600 text-white py-3 rounded-full font-semibold transition opacity-50 cursor-not-allowed"
                disabled
            >
                REGISTER
            </button>

        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
        
            const form = document.querySelector("form");
            const submitBtn = document.getElementById("registerBtn");
        
            const fields = {
                firstname: {
                    el: document.querySelector("input[name='firstname']"),
                    errorEl: null,
                    validate: (v) => {
                        if (v.trim() === "") return "First name is required.";
                        if (!/^[a-zA-Z0-9 ]+$/.test(v)) return "Only letters and numbers allowed.";
                        return true;
                    }
                },
                lastname: {
                    el: document.querySelector("input[name='lastname']"),
                    errorEl: null,
                    validate: (v) => {
                        if (v.trim() === "") return "Last name is required.";
                        if (!/^[a-zA-Z0-9 ]+$/.test(v)) return "Only letters and numbers allowed.";
                        return true;
                    }
                },
                email: {
                    el: document.querySelector("input[name='email']"),
                    errorEl: null,
                    validate: (v) => /\S+@\S+\.\S+/.test(v) || "Enter a valid email.",
                },
                phone: {
                    el: document.querySelector("input[name='phone']"),
                    errorEl: null,
                    validate: (v) =>
                        /^\+?[0-9]{7,15}$/.test(v) || "Enter a valid phone number.",
                },
                password: {
                    el: document.querySelector("input[name='password']"),
                    errorEl: null,
                    validate: (v) => v.length >= 8 || "Password must be at least 8 characters.",
                },
                password_confirmation: {
                    el: document.querySelector("input[name='password_confirmation']"),
                    errorEl: null,
                    validate: (v) =>
                        v === document.querySelector("input[name='password']").value ||
                        "Passwords do not match.",
                }
            };
        
            // Attach error containers dynamically
            Object.values(fields).forEach(field => {
                const p = document.createElement("p");
                p.classList.add("text-red-500", "text-xs", "ml-3", "hidden" , "font-bold");
                field.el.parentElement.after(p);
                field.errorEl = p;
        
                // Live validation
                field.el.addEventListener("input", () => {
                    validateField(field);
                    updateSubmitButton();
                });
            });
        
            function validateField(field) {
                const value = field.el.value;
                const result = field.validate(value);
        
                if (result !== true) {
                    field.errorEl.textContent = result;
                    field.errorEl.classList.remove("hidden");
                    field.el.classList.add("border-red-500");
                    return false;
                }
        
                field.errorEl.classList.add("hidden");
                field.el.classList.remove("border-red-500");
                return true;
            }
        
            function updateSubmitButton() {
                const allValid = Object.values(fields).every(field => validateField(field));
        
                if (allValid) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove("opacity-50", "cursor-not-allowed");
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.add("opacity-50", "cursor-not-allowed");
                }
            }
        
            // Validate on submit (double safety)
            form.addEventListener("submit", (e) => {
                let isValid = true;
        
                Object.values(fields).forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });
        
                if (!isValid) {
                    e.preventDefault();
                    updateSubmitButton();
                }
            });
        
        });
        </script>
        
</body>
</html>
