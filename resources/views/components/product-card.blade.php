<div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer">

    <a href="{{ url('/product/' . $product->id) }}">

        <img src="{{ asset('storage/' . $product->image) }}"
             class="w-full h-40 object-cover rounded"
             alt="{{ $product->name }}">

        <h3 class="mt-3 font-medium text-gray-700">
            {{ $product->name }}
        </h3>

        <p class="text-orange-600 font-semibold mt-1">
            ₹{{ number_format($product->price, 2) }}
        </p>

    </a>

</div>
