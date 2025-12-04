@extends('layouts.wishlist-layout')

@section('title', 'Wishlist')

@section('content')

@include('components.navbar')

{{-- @php
    dd($wishlistProducts);
@endphp --}}

@include('components.wishlist', ['wishlistProducts' => $wishlistProducts])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
