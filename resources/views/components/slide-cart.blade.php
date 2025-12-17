

<!-- Overlay -->
<div id="cart-overlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="closeCart()"></div>

<!-- Slide Cart -->
<aside id="slide-cart"
       class="fixed right-0 top-0 h-full w-[420px] bg-white shadow-lg z-50 transform translate-x-full transition-transform duration-300 flex flex-col">

    <!-- Header -->
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">Your Cart</h2>
        <button onclick="closeCart()" class="text-xl">✕</button>
    </div>

    <!-- CART ITEMS -->
    <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-6">

        @if(empty($cartitems) || count($cartitems) === 0)
            <p id="cart-empty-msg" class="text-gray-500">Your cart is empty</p>

        @else
            @foreach ($cartitems as $lineId => $item)

                <div class="flex items-center justify-between mb-3 border-b pb-3">

                    <!-- Product -->
                    <div class="flex items-center space-x-3">
                        <img src="{{ $item->image }}" class="w-16 h-16 rounded object-cover" />
                        <span class="font-medium w-36">{{ $item->name }}</span>
                    </div>

                    <!-- Controls -->
                    <div class="flex flex-col items-end gap-2 text-right">

                        <p class="font-semibold">₹{{ $item->price * $item->quantity }}</p>

                        <!-- Qty -->
                        <div class="flex items-center space-x-2">

                            {{-- <form method="POST" action="{{ route('cart.decrement', $lineId) }}">
                                @csrf --}}
                                <button class="px-2 py-1 bg-gray-200 rounded" onclick="decrementQuantity('{{ $item->product_id }}',{{$item->quantity}})">-</button>
                            {{-- </form> --}}

                            <span id="qty-{{ $item->product_id }}">{{ $item->quantity }}</span>

                            {{-- <form method="POST" action="{{ route('cart.increment', $lineId) }}">
                                @csrf --}}
                                <button class="px-2 py-1 bg-gray-200 rounded" onclick="incrementQuantity('{{ $item->product_id }}',{{$item->quantity}})">+</button>
                            {{-- </form> --}}

                        </div>

                        <!-- Remove -->
                        {{-- <form method="POST" action="onClic">
                            @csrf --}}
                            <button class="text-red-500 text-sm" onclick="removeFromCart('{{ $item->product_id }}')">Remove</button>
                        {{-- </form> --}}

                    </div>
                </div>

            @endforeach
        @endif

    </div>

    <p id="cart-empty-msg" class="text-gray-500 hidden">Your cart is empty</p>

    <!-- FOOTER: TOTALS -->
    @php
        $subtotal = !empty($cartitems)
            ? collect($cartitems)->sum(fn($i) => $i->price * $i->quantity)
            : 0;
    @endphp

<div class="p-4 border-t">

    <div class="flex justify-between font-medium">
        <span>Cart Total:</span>
        <span id="cart-total">₹{{ $subtotal }}</span>
    </div>
    <div class="flex justify-between font-medium mt-4">
        <span>Subtotal:</span>
        <span id="cart-subtotal">₹{{ $subtotal }}</span>
    </div>

    <button id="checkout-btn" @if(empty($cartitems) || count($cartitems) === 0) disabled @endif
           class="w-full block text-center py-2 rounded-lg mt-3 bg-blue-600 hover:bg-blue-700 text-white @if(empty($cartitems) || count($cartitems) === 0) disabled:bg-gray-400 disabled:cursor-not-allowed @endif">
        Checkout
    </button>
</div>

</aside>

