@extends('layouts.payment-success-layout')

@section('title', 'Payment Success')

@section('content')

@php
$cartitems = []; // Empty cart for payment success page
@endphp
@include('components.navbar',['removeCartIcon' => true])

@include('components.payment-success', ['userInfoDetails' => $userInfoDetails, 'orderDetails' => $orderDetails])

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}


@include('components.footer')


@endsection
