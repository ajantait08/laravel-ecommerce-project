
            {{-- Products Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mt-6 pb-14" id="productsContainer">
                @forelse ($paginator as $product)
                    @include('components.product-card', ['product' => $product])
                @empty
                    <p class="col-span-full text-center text-gray-500">
                        No products found
                    </p>
                @endforelse
            </div>
            {{-- Pagination --}}
@include('components.custom-pagination', ['paginator' => $paginator])