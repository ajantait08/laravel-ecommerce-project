<div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer">

    <a href="{{ url('/product/' . $product->_id) }}">

        {{-- Product Image --}}
        <img src="{{ $product->images[0] ?? $product->image[0] ?? '/default.jpg' }}"
             alt="{{ $product->name }}"
             class="w-full h-40 object-cover rounded">

             <button
             onclick="event.preventDefault(); toggleWishlist('{{ $product->_id }}', '{{ $product->name }}');"
             class="right-2 bg-white p-2 rounded-full shadow-md hover:scale-110 transition"
         >
             {{-- Dynamic Heart Icon --}}
             @if(!empty($product->is_wishlisted) && $product->is_wishlisted)
                <img id="wish-icon-{{ $product->_id }}" 
                    data-wished="true"
                    src="{{ asset('assets/heart_filled_icon.svg') }}" 
                    class="w-4 h-4" alt="wishlisted">
            @else
                <img id="wish-icon-{{ $product->_id }}" 
                    data-wished="false"
                    src="{{ asset('assets/heart_icon.svg') }}" 
                    class="w-4 h-4" alt="wishlist">
            @endif
         </button>

        {{-- Product Name --}}
        <h3 class="mt-3 font-medium text-gray-700 truncate">
            {{ $product->name }}
        </h3>

        {{-- Product Description (Optional) --}}
        @if(!empty($product->description))
            <p class="text-xs text-gray-500/70 truncate">
                {{ $product->description }}
            </p>
        @endif

        {{-- Rating --}}
        <div class="flex items-center gap-2 mt-1">
            <p class="text-xs">4.5</p>
            <div class="flex items-center gap-0.5">
                @for ($i = 0; $i < 5; $i++)
                    @if ($i < 4)
                        <img src="{{ asset('assets/star_icon.svg') }}" class="h-3 w-3" alt="star">
                    @else
                        <img src="{{ asset('assets/star_dull_icon.svg') }}" class="h-3 w-3" alt="star">
                    @endif
                @endfor
            </div>
        </div>

        {{-- Price --}}
        <p class="text-orange-600 font-semibold mt-1">
            ₹{{ number_format($product->price, 2) }}
        </p>

    </a>

</div>
