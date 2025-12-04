@extends('layouts.collection-layout')

@section('title', 'Collections')

@section('content')

@include('components.navbar',['cartitems' => $cartitems])

{{-- @php
    dd($filteredProducts);


@include('components.collection', [ 'categories' => $categories,'activeCategory' => $activeCategory,'filteredProducts' => $filteredProducts])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
