@extends('layouts.product-details-layout')

@section('title', 'All Products')

@section('content')

@include('components.navbar' ,  ['cartitems' => $cartitems])

{{-- @php
    dd($wishlistProducts);
@endphp --}}

@include('components.product-details', ['product' => $product])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
