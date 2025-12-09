@php
    $user = session('user');
    //dd($user);                 // logged-in user from session
    $cart = session('cart', []);
    if($cartitems){            // cart from session    
    $cartCount = count($cartitems); // total items
    }
    else{
    $cartCount = 0;
    }
@endphp

<nav class="flex items-center justify-between px-6 md:px-16 lg:px-32 py-3 border-b border-gray-300 text-gray-700">

    {{-- Logo --}}
    <a href="/">
        <img src="{{ asset('assets/logo.svg') }}" class="cursor-pointer w-28 md:w-32" alt="logo">
    </a>

    {{-- Links --}}
    <div class="flex items-center gap-4 lg:gap-8 max-md:hidden">

        <a href="{{ $user ? '/dashboard' : '/' }}" class="hover:text-gray-900 transition">Home</a>
        <a href="/all-products" class="hover:text-gray-900 transition">Shop</a>

        {{-- Wishlist - redirect to login if not logged in --}}
        <a href="{{ $user ? '/wishlist' : '/login' }}" class="hover:text-gray-900 transition">
            Wishlist
        </a>

        <a href="/about" class="hover:text-gray-900 transition">About Us</a>

        {{-- Contact --}}
        <a href="{{ $user ? '/contact' : '/login' }}" class="hover:text-gray-900 transition">
            Contact
        </a>

        {{-- Cart Icon --}}
        @if (!session('isBuyNowActive'))
            <button onclick="openCart()" class="relative text-xl">
                🛒               
                    <span id="cart-items-count" class="{{ $cartCount > 0 ? 'block' : 'hidden' }} absolute -top-2 -right-2 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        {{ $cartCount }}
                    </span>                
            </button>
        @endif

        {{-- Slide Cart Component --}}
        @include('components.slide-cart',['cartitems' => $cartitems ?? []])

        {{-- Seller Dashboard --}}
        @if (session('isSeller'))
            <a href="/seller" class="text-xs border px-4 py-1.5 rounded-full">
                Seller Dashboard
            </a>
        @endif

        {{-- User Auth Section --}}
        @if ($user)
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 border px-3 py-1 rounded-full cursor-pointer hover:bg-gray-100 transition"
                    style="background-color: orange;">
                    <img src="{{ $user['avatar'] ?? asset('assets/user_icon.svg') }}"
                         alt="user"
                         width="24"
                         height="24"
                         class="rounded-full">
                    <span class="text-sm font-medium">{{ $user['name'] ?? 'User' }}</span>
                </div>

                <a href="/logout"
                    class="text-xs border border-gray-400 px-3 py-1.5 rounded-full hover:bg-gray-100">
                    Logout
                </a>
            </div>
        @else
            <a href="/login"
                class="text-xs border border-gray-400 px-4 py-1.5 rounded-full hover:bg-gray-100">
                Login / Register
            </a>
        @endif

    </div>
</nav>

{{-- JS for Opening Cart --}}
{{-- <script>
    function toggleCart() {
        const cartElement = document.getElementById('slideCart');
        cartElement.classList.toggle('hidden');
        cartElement.classList.toggle('translate-x-0');
    }
</script> --}}
