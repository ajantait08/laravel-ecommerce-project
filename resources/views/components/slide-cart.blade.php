<div id="slideCart"
     class="fixed top-0 right-0 w-72 h-full bg-white shadow-xl p-4 hidden translate-x-full transition-all duration-300">

    <h3 class="text-lg font-semibold mb-4">Your Cart</h3>

    @if (count(session('cart', [])) == 0)
        <p class="text-gray-500">Your cart is empty.</p>
    @else
        @foreach (session('cart') as $item)
            <div class="border-b py-2">
                <p class="font-medium">Product ID: {{ $item['product_id'] }}</p>
                <p>Qty: {{ $item['qty'] }}</p>
            </div>
        @endforeach

        <a href="/cart"
           class="block text-center mt-4 bg-black text-white py-2 rounded">
            View Cart
        </a>
    @endif
</div>
