@extends('layouts.product-details-layout')

@section('content')

@include('components.navbar')

<div class="px-6 md:px-16 lg:px-32 pt-14 space-y-10">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-16">

        {{-- Left: Product Images --}}
        <div class="px-5 lg:px-16 xl:px-20">
            <div class="rounded-lg overflow-hidden bg-gray-500/10 mb-4">
                <img 
                    src="{{ asset($product->images[0]) }}"
                    alt="{{ $product->name }}"
                    class="w-full h-auto object-cover mix-blend-multiply"
                />
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div class="cursor-pointer rounded-lg overflow-hidden bg-gray-500/10 hover:opacity-80 transition">
                    <img 
                        src="{{ asset($product->images[0]) }}"
                        alt="thumb"
                        class="w-full h-auto object-cover mix-blend-multiply"
                    />
                </div>
            </div>
        </div>

        {{-- Right: Product Details --}}
        <div class="flex flex-col">

            <h1 class="text-3xl font-medium text-gray-800/90 mb-4">
                {{ $product->name }}
            </h1>

            <p class="text-gray-600 mt-3">{{ $product->description }}</p>

            <p class="text-3xl font-medium mt-6">
                {{ '₹' }}{{ $product->price }}
            </p>

            <hr class="bg-gray-600 my-6" />

            {{-- Buttons --}}
            <div class="flex items-center mt-10 gap-4">

                {{-- Add to Cart --}}
                {{-- <form action="{{ route('cart.add', $product->id) }}" method="POST"> --}}
                    {{-- @csrf --}}
                    <button class="px-8 py-2 bg-orange-600 rounded text-white hover:bg-orange-700 transition" onclick="addToCart('{{ $product->_id }}', 1)">
                        Add to Cart
                    </button>
                {{-- </form> --}}

                {{-- Buy Now --}}
                {{-- <form action="{{ route('checkout.buyNow', $product->id) }}" method="POST"> --}}
                <form action="" method="POST">
                    @csrf
                    <button 
                        class="px-8 py-2 rounded text-white font-semibold 
                        bg-gradient-to-r from-orange-500 to-red-500 
                        hover:from-red-500 hover:to-orange-500 
                        shadow-lg shadow-orange-300/40 transition-all">
                        Buy Now
                    </button>
                </form>

            </div>

        </div>
    </div>

    {{-- Recently Viewed Section --}}
    <div class="flex flex-col items-center mt-16">
        <p class="text-3xl font-medium">
            Recently Viewed <span class="text-orange-600">Products</span>
        </p>
        <div class="w-28 h-0.5 bg-orange-600 mt-2 mb-6"></div>

        <p id="no-recent" class="text-gray-500 hidden">No recently viewed products yet.</p>

        <div id="recent-section" class="w-full pb-12 relative hidden">

            <div class="absolute left-0 top-1/2 -translate-y-1/2 z-20">
                <button class="swiper-button-prev-custom bg-white shadow-md p-2 rounded-full" 
                    style="background-color:rgb(233, 147, 147)">
                    ◀
                </button>
            </div>

            <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20">
                <button class="swiper-button-next-custom bg-white shadow-md p-2 rounded-full"
                    style="background-color:rgb(233, 147, 147)">
                    ▶
                </button>
            </div>

            <div class="swiper mySwiper w-full py-2 px-1">
                <div id="recent-wrapper" class="swiper-wrapper flex flex-row overflow-x-auto space-x-4">
                    {{-- Injected dynamically via JS --}}
                </div>
            </div>
        </div>

    </div>

</div>

@include('components.footer')

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

{{-- Manage Recent Products via localStorage --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    const productId = "{{ $product->id }}";

    // ---- SAVE RECENTLY VIEWED ----
    let viewed = JSON.parse(localStorage.getItem("recently_viewed")) || [];

    viewed = viewed.filter(id => id !== productId);
    viewed.unshift(productId);
    viewed = viewed.slice(0, 10);

    localStorage.setItem("recently_viewed", JSON.stringify(viewed));

    // ---- LOAD RECENT PRODUCTS ----
    let recent = viewed.filter(id => id !== productId);

    if (recent.length === 0) {
        document.getElementById("no-recent").classList.remove("hidden");
        return;
    }

    document.getElementById("recent-section").classList.remove("hidden");

    // Fetch product details from backend
    fetch("{{ route('products.apiBulk') }}?ids=" + recent.join(","))
        .then(res => res.json())
        .then(data => {
            const wrapper = document.getElementById("recent-wrapper");

            data.forEach(p => {
                wrapper.innerHTML += `
                    <div class="swiper-slide !m-0 !p-0">
                        <a href="/product/${p.id}">
                            <div class="border rounded-lg p-3 bg-white shadow">
                                <img src="${p.images[0]}" class="w-full h-80 object-cover rounded" />
                                <p class="mt-2 font-medium">${p.name}</p>
                                <p class="text-orange-600 font-semibold">₹${p.price}</p>
                            </div>
                        </a>
                    </div>
                `;
            });

            new Swiper(".mySwiper", {
                slidesPerView: 2,
                spaceBetween: 6,
                autoplay: {
                    delay: 1800,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next-custom",
                    prevEl: ".swiper-button-prev-custom",
                },
                breakpoints: {
                    320: { slidesPerView: 1.3 },
                    640: { slidesPerView: 2.2 },
                    1024: { slidesPerView: 3.2 },
                }
            });
        });
});
</script>

@endsection
