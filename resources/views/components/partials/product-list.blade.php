<div class="grid grid-cols-2 md:grid-cols-4 gap-4">

    @forelse ($products as $product)
        @include('components.product-card', ['product' => $product])
    @empty
        <p class="col-span-full text-center text-gray-500">
            No products found
        </p>
    @endforelse

</div>

{{-- Pagination --}}
<div class="mt-6">
    {{ $products->links() }}
</div>
