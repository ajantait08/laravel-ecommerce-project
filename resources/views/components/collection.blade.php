@extends('layouts.collection-layout')

@section('content')

@include('components.navbar')

<div class="flex flex-col items-start px-6 md:px-16 lg:px-32">

    {{-- Header --}}
    <div class="flex flex-col pt-12 w-full">
        <p class="text-2xl font-medium">Product Collections</p>
        <div class="w-16 h-0.5 bg-orange-600 rounded-full"></div>
    </div>

    {{-- Category Tabs --}}
    <div class="flex flex-wrap gap-3 mt-8 border-b pb-3 w-full">
        @foreach($categories as $cat)
            <a 
                href="{{ route('collections.show', ['category' => $cat]) }}"
                class="px-5 py-2 rounded-full text-sm md:text-base transition-all duration-200
                    {{ $activeCategory === $cat 
                        ? 'bg-orange-600 text-white shadow-md' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                {{ $cat }}
            </a>
        @endforeach
    </div>

    {{-- Product Grid --}}
    <div class="relative w-full min-h-[300px]">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mt-10 pb-14 w-full">
            
            @if(count($filteredProducts) > 0)
                @foreach($filteredProducts as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            @else
                <div class="col-span-full text-center text-gray-500 text-lg">
                    No products found in this category.
                </div>
            @endif

        </div>
    </div>

</div>

@include('components.footer')

@endsection
