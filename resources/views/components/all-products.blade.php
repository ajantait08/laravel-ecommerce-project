@extends('layouts.all-products-layout')

@section('content')

    {{-- Navbar --}}
    @include('components.navbar')

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

    {{-- Footer --}}
    @include('components.footer')

@endsection
