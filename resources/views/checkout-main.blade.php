@extends('layouts.checkout-layout')

@section('title', 'Checkout')

@section('content')

@include('components.navbar',['cartitems' => $cartitems])

{{-- @php
    dd($wishlistProducts);
@endphp --}}

@include('components.checkout', ['cartitems' => $cartitems])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
