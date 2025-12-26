@extends('layouts.product-details-layout')

@section('content')

@include('components.navbar')

@php
$user = session('user');
@endphp

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

    {{-- ================= REVIEWS & RATINGS ================= --}}
<div class="mt-20">

    <h2 class="text-3xl font-medium text-gray-800">
        Customer <span class="text-orange-600">Reviews</span>
    </h2>
    <div class="w-24 h-0.5 bg-orange-600 mt-2 mb-8"></div>

    {{-- Rating Summary --}}
    <div class="flex items-center gap-6 mb-10">
        <div class="text-5xl font-semibold text-gray-800">
            {{ $averageRating ?? '0.0' }}
        </div>

        <div>
            <div class="flex items-center gap-1">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= floor($averageRating) ? 'text-orange-500' : 'text-gray-300' }}"
                         fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.287 3.97c.3.921-.755 1.688-1.54 1.118l-3.378-2.455a1 1 0 00-1.175 0l-3.378 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.97a1 1 0 00-.364-1.118L2.05 9.397c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69l1.287-3.97z"/>
                    </svg>
                @endfor
            </div>

            <p class="text-gray-500 text-sm mt-1">
                Based on {{ count($reviews) }} reviews
            </p>
        </div>
    </div>

    <br>

    {{-- Write Review --}}
@if($user)
<div class="bg-gray-50 p-6 rounded-lg mb-14 max-w-xl">
    <h3 class="text-xl font-medium mb-4">Write a Review</h3>

    <form id="reviewForm">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->_id }}">
        <input type="hidden" name="rating_value" id="ratingValue">

        {{-- Star Selector --}}
        <div class="flex items-center gap-2 mb-4" id="starSelector">
            @for($i = 1; $i <= 5; $i++)
                <svg data-star="{{ $i }}"
                     class="w-8 h-8 cursor-pointer text-gray-300 hover:text-orange-500"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.287 3.97c.3.921-.755 1.688-1.54 1.118l-3.378-2.455a1 1 0 00-1.175 0l-3.378 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.97a1 1 0 00-.364-1.118L2.05 9.397c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69l1.287-3.97z"/>
                </svg>
            @endfor
        </div>

        <textarea name="review_text"
                  class="w-full border rounded p-3"
                  rows="4"
                  placeholder="Share your experience..."
                  required></textarea>

        <button type="submit"
                class="mt-4 px-6 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
            Submit Review
        </button>
    </form>
</div>
@else
<p class="text-gray-600 mb-4">
    Please log in to rate and review this product.
</p>

<a href="{{ route('login', ['redirect' => url()->current()]) }}"
   class="inline-block px-6 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
    Rate The Product
</a>
@endif

<br><br><br><br>
    {{-- Reviews List --}}
    @if(count($reviews) === 0)
        <p class="text-gray-500">No reviews yet. Be the first to review this product.</p>
    @else
        <div class="space-y-8">
            @foreach($reviews as $review)
                <div class="border-b pb-6">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-800">
                            @php
                                $user_id = $review->user_id;
                                $user_name_details = DB::select('select firstname,lastname from users where id = ?',[$user_id]);
                                //print_r($user_name_details);
                                $username = '';
                                if($user_name_details[0]->firstname != ''){
                                    $username = $user_name_details[0]->firstname;
                                }
                                else{
                                    $username = '';  
                                }

                                if($user_name_details[0]->lastname != ''){
                                    $username .= ' '.$user_name_details[0]->lastname;
                                }
                                else{
                                    $username .= '';  
                                }
                            @endphp
                            User #{{ $username }}
                        </p>
                        <span class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($review->review_date)->format('d M Y') }}
                        </span>
                    </div>

                    {{-- Stars --}}
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-orange-500' : 'text-gray-300' }}"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.287 3.97c.3.921-.755 1.688-1.54 1.118l-3.378-2.455a1 1 0 00-1.175 0l-3.378 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.97a1 1 0 00-.364-1.118L2.05 9.397c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69l1.287-3.97z"/>
                            </svg>
                        @endfor
                    </div>

                    <p class="mt-3 text-gray-600">
                        {{ $review->review }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
{{-- ================= END REVIEWS ================= --}}


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

<script>
    document.addEventListener("DOMContentLoaded", () => {
    
        const stars = document.querySelectorAll("#starSelector svg");
        const ratingInput = document.getElementById("ratingValue");
        const reviewForm = document.getElementById("reviewForm");
    
        stars.forEach(star => {
            star.addEventListener("click", () => {
                ratingInput.value = star.dataset.star;
    
                stars.forEach(s => {
                    s.classList.toggle("text-orange-500", s.dataset.star <= ratingInput.value);
                    s.classList.toggle("text-gray-300", s.dataset.star > ratingInput.value);
                });
            });
        });
    
        reviewForm.addEventListener("submit", function (e) {
            e.preventDefault();
    
            if (!ratingInput.value) {
                alert("Please select a rating");
                return;
            }
    
            const payload = {
                product_id: reviewForm.querySelector('input[name="product_id"]').value,
                rating_value: ratingInput.value,
                review_text: reviewForm.querySelector('textarea[name="review_text"]').value
            };
    
            fetch("{{ route('reviews.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reviewForm').reset();
                    alert("Review submitted successfully!");
                    location.reload();
                }
            })
            .catch(() => alert("Something went wrong"));
        });
    });
    </script>
    




@include('components.footer')


{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
@endsection
