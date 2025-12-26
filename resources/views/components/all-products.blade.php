@extends('layouts.all-products-layout')

@section('content')

    {{-- Navbar --}}
    @include('components.navbar')

    @php

    /*
    <div class="flex flex-col items-start px-6 md:px-16 lg:px-32">

        {{-- Header --}}
        <div class="flex flex-col items-end pt-12">
            <p class="text-2xl font-medium">All products List</p>
            <div class="w-16 h-0.5 bg-orange-600 rounded-full"></div>
        </div>

        {{-- Products Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 flex-col items-center gap-6 mt-12 pb-14 w-full">
            @foreach ($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>

    </div>

    */
    @endphp

    <div class="flex gap-8 px-6 md:px-16 pt-12">

        {{-- Filters Sidebar --}}
        <aside class="w-64 hidden md:block border rounded-lg p-4 bg-white">
            @include('components.product-filters',['category' => $category , 'brand' => $brand])
        </aside>

        {{-- Products Section --}}

        <div class="flex-1">

            {{-- Sorting Bar --}}
            
            @include('components.products-sorting', ['min_price' => $min_price, 'max_price' => $max_price])
            {{-- Products Grid --}}
            <div id="updatedProductsSection">
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
            
        </div>

        </div>
    </div>

    
    


    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {           
        //let sortValue = 'popularity';
        
        //const form = document.getElementById('filterForm');
        const productContainer = document.getElementById('productContainer');

        const minRange = document.getElementById('minRange');
        const maxRange = document.getElementById('maxRange');
        
        function fetchProducts() {
            //const formData = new FormData(form);
           //formData.append('sort', sortValue);
        
            fetch("{{ route('products.index') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: {
                    minRange: minRange,
                    maxRange: maxRange
                }
            })
            .then(res => res.text())
            .then(html => {
                productContainer.innerHTML = html;
            });
        }
        
        // Trigger on checkbox change
        form.querySelectorAll('input').forEach(el => {
            el.addEventListener('change', fetchProducts);
        });
        
        // Sorting
        document.querySelectorAll('[data-sort]').forEach(btn => {
            btn.addEventListener('click', () => {
                sortValue = btn.dataset.sort;
                fetchProducts();
            });
        });
        
        // PRICE SLIDER
        
        
        minRange.oninput = () => {
            min_price.value = minRange.value;
            minVal.innerText = `₹${minRange.value}`;
            fetchProducts();
        };
        
        maxRange.oninput = () => {
            max_price.value = maxRange.value;
            maxVal.innerText = `₹${maxRange.value}+`;
            fetchProducts();
        };
    });
        </script> --}}
        

    {{-- Footer --}}
    @include('components.footer')

@endsection
