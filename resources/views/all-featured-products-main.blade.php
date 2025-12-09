@extends('layouts.all-featured-products-layout')

@section('title', 'All Featured Products')

@section('content')

@include('components.navbar',['cartitems' => $cartitems])

{{-- @php
    dd($wishlistProducts);
@endphp --}}

@include('components.all-featured-products', ['products' => $products])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
