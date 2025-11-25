<div class="flex flex-col items-center pt-14">

    <p class="text-2xl font-medium text-left w-full">Popular products</p>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mt-6 pb-14 w-full">

        @foreach($products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach

    </div>

    <a href="{{ url('/all-products') }}"
       class="px-12 py-2.5 border rounded text-gray-500/70 hover:bg-slate-50/90 transition">
        See more
    </a>

</div>
