

<div class="min-h-screen flex items-center justify-center px-4 bg-gray-50">
    <div class="bg-white shadow-lg rounded-2xl p-8 max-w-xl w-full">

        {{-- Success Header --}}
        <div class="text-center">
            <div class="text-green-500 text-6xl mb-4">✔</div>
            <h1 class="text-2xl font-semibold mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-4">
                Thank you! Your order has been placed successfully.
            </p>
        </div>

        {{-- Customer Info --}}
        <p><strong>Order ID:</strong> {{ $userInfoDetails[0]->payment_intent_id }}</p>
        <p><strong>Name:</strong> {{ $userInfoDetails[0]->first_name }} {{ $userInfoDetails[0]->last_name }}</p>
        <p><strong>Email:</strong> {{ $userInfoDetails[0]->email }}</p>
        <p class="mb-4">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($userInfoDetails[0]->payment_date_time)->format('d M Y, h:i A') }}
        </p>

        <hr class="my-4">

        {{-- Shipping Address --}}
        <h3 class="font-semibold mb-2">Shipping Address</h3>
        {{-- <p>{{ $userInfoDetails[0]->shipping_name }}</p> --}}
        <p>{{ $userInfoDetails[0]->address }}</p>

        {{-- @if(!empty($userInfoDetails[0]->shipping_address2))
            <p>{{ $userInfoDetails[0]->shipping_address2 }}</p>
        @endif --}}

        <p>
            {{ $userInfoDetails[0]->city }},
            {{ $userInfoDetails[0]->state }}
        </p>
        <p>
            {{ $userInfoDetails[0]->country }} - {{ $userInfoDetails[0]->pincode }}
        </p>

        {{-- Order Items --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-3">Order Items</h3>

            @foreach($orderDetails as $item)
                <div class="flex justify-between border-b py-3">
                    <div>
                        <p class="font-medium">{{ $item->name }}</p>
                        <p class="text-sm text-gray-500">
                            Qty: {{ $item->quantity }}
                        </p>
                    </div>
                    <p>₹{{ number_format($item->price, 2) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Pricing --}}
        <div class="mt-6 border-t pt-4">
            <div class="flex justify-between mb-2">
                <span>Subtotal</span>
                <span>₹{{ number_format($userInfoDetails[0]->total_amount, 2) }}</span>
            </div>

            <div class="flex justify-between mb-2">
                <span>Shipping</span>
                <span>₹{{ number_format($userInfoDetails[0]->shipping_cost, 2) }}</span>
            </div>

            <div class="flex justify-between font-semibold text-lg border-t pt-3">
                <span>Total</span>
                <span>₹{{ number_format($userInfoDetails[0]->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- CTA --}}
        <div class="mt-6 text-center">
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center bg-green-500 text-white px-5 py-2 rounded-full">
                Continue Shopping →
            </a>
        </div>
    </div>
</div>
