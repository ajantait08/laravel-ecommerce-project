<div class="flex flex-col items-center pt-14">

    <div class="flex flex-col items-center">
        <p class="text-3xl font-medium">Popular Products</p>
        <div class="w-28 h-0.5 bg-orange-600 mt-2"></div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mt-6 pb-14 w-full">
        {{-- @php
            
           dd($products);
            
        @endphp --}}
        @foreach($products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach

    </div>

    <a href="{{ url('/all-products') }}"
       class="px-12 py-2.5 border rounded text-gray-500/70 hover:bg-slate-50/90 transition">
        See more
    </a>

</div>
