@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

@include('components.navbar')

{{-- <div class="p-6">
    <h1 class="text-2xl font-bold">Welcome to Dashboard</h1>
</div> --}}

@include('components.header-slider')

@endsection
