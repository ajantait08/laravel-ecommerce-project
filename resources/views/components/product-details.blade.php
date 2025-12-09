@extends('layouts.product-details-layout')

@section('content')

@include('components.navbar')

<style>
    .carousel-arrow {
    font-size: 48px;
    text-shadow: 2px 0 currentColor, -2px 0 currentColor;
    cursor: pointer;
    user-select: none;
}
</style>

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
{{-- Recently Viewed Section --}}
<div class="flex flex-col items-center mt-16">
    <p class="text-3xl font-medium">
        Recently Viewed <span class="text-orange-600">Products</span>
    </p>
    <div class="w-28 h-0.5 bg-orange-600 mt-2 mb-6"></div>

    @php
        $recentCount = count($recentProducts);
    @endphp

    @if($recentCount === 0)
        <p class="text-gray-500">No recently viewed products yet.</p>
    @else
        <div id="recent-section" class="w-full pb-12 relative">

            {{-- LEFT ARROW --}}
            <button id="recent-prev"
                class="carousel-arrow absolute -left-16 top-1/2 -translate-y-1/2 z-20 
                    w-12 h-12 flex items-center justify-center
                    bg-white text-black text-3xl font-extrabold rounded-full shadow 
                    hover:text-black transition 
                    hidden disabled:opacity-30 disabled:bg-gray-200 disabled:cursor-not-allowed">
                ←
            </button>

            {{-- RIGHT ARROW --}}
            <button id="recent-next"
                class="carousel-arrow absolute -right-16 top-1/2 -translate-y-1/2 z-20 
                    w-12 h-12 flex items-center justify-center
                    bg-white text-black text-3xl font-extrabold rounded-full shadow 
                    hover:text-black transition 
                    hidden disabled:opacity-30 disabled:bg-gray-200 disabled:cursor-not-allowed">
                →
            </button>

            {{-- Scroll Container --}}
            <div class="recent-scroll-container overflow-x-hidden w-full">
                <div id="recent-wrapper" class="flex space-x-5 transition-all duration-300">

                    {{-- Rendered from server side --}}
                    @foreach($recentProducts as $p)
                        <div class="border rounded-lg p-3 bg-white shadow cursor-pointer min-w-[240px] max-w-[240px]">
                            <a href="/product/{{ $p->_id }}">
                                <img src="{{ asset($p->images[0]) }}" class="w-full h-64 object-cover rounded" />
                                <p class="mt-2 font-medium">{{ $p->name }}</p>
                                <p class="text-orange-600 font-semibold">₹{{ $p->price }}</p>
                            </a>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    @endif
</div>

{{-- Carousel Logic --}}
@if($recentCount > 4)
<script>
document.addEventListener("DOMContentLoaded", function () {

    const scrollContainer = document.querySelector(".recent-scroll-container");
    const prevBtn = document.getElementById("recent-prev");
    const nextBtn = document.getElementById("recent-next");

    // Show arrows only if > 4 items
    prevBtn.classList.remove("hidden");
    nextBtn.classList.remove("hidden");

    // Arrow click events
    prevBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: -260, behavior: "smooth" });
    });

    nextBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: 260, behavior: "smooth" });
    });

    // Arrow enable/disable logic
    function updateArrows() {
        const maxScroll = scrollContainer.scrollWidth - scrollContainer.clientWidth;

        prevBtn.disabled = scrollContainer.scrollLeft <= 5;
        nextBtn.disabled = scrollContainer.scrollLeft >= maxScroll - 5;

        prevBtn.classList.toggle("opacity-30", prevBtn.disabled);
        prevBtn.classList.toggle("cursor-not-allowed", prevBtn.disabled);

        nextBtn.classList.toggle("opacity-30", nextBtn.disabled);
        nextBtn.classList.toggle("cursor-not-allowed", nextBtn.disabled);
    }

    scrollContainer.addEventListener("scroll", () => {
        window.requestAnimationFrame(updateArrows);
    });

    // Initial state
    updateArrows();
});
</script>
@endif



@include('components.footer')


{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
@endsection
