@extends('layouts.master')

@section('title', 'Lead Page')

@section('content')

@include('components.navbar',['cartitems' => $cartitems])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}

@include('components.header-slider')

@include('components.homeproducts', ['products' => $products])

@include('components.featured-products')

@include('components.banner')

@include('components.footer')


@endsection
