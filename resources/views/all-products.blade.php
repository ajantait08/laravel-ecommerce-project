@extends('layouts.all-products-layout')

@section('title', 'All Products')

@section('content')

@include('components.navbar',['cartitems' => $cartitems])

{{-- @php
    dd($wishlistProducts);
@endphp --}}

@include('components.all-products', ['products' => $products])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
