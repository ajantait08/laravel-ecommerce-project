<div class="min-h-screen flex flex-col px-6 md:px-10 lg:px-20 py-10">

    <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

    {{-- If wishlist is empty --}}
    @if($wishlistProducts->isEmpty())
        <div class="flex flex-col items-center justify-center text-center py-20">

            <img src="{{ asset('assets/empty_wishlist_image.png') }}"
                 alt="empty wishlist"
                 class="mb-6 opacity-80 w-96">

            <a href="/collections/All"
               class="px-5 py-2 bg-black text-white text-sm rounded-full hover:bg-gray-800 transition">
                Browse Products
            </a>
        </div>

    @else

        {{-- Wishlist Products Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">

            @foreach($wishlistProducts as $product)
                <div class="relative">

                    {{-- Reuse your product card --}}
                    @include('components.product-card', ['product' => $product])

                    {{-- Remove from wishlist --}}
                    <button
                        onclick="removeFromWishlist('{{ $product->_id }}','{{ $product->name }}')"
                        class="absolute top-3 left-3 bg-white/90 hover:bg-red-100 p-1.5 rounded-full shadow-md transition"
                    >
                        <img src="{{ asset('assets/delete_wishlist.png') }}"
                             alt="remove wishlist"
                             class="w-4 h-4">
                    </button>

                </div>
            @endforeach

        </div>

    @endif
</div>


{{-- JS for removing wishlist item --}}
<script>
function removeFromWishlist(productId,productName) {
    const userId = "{{ session('user.id') }}";
    const userEmail = "{{ session('user.email') }}";

    if (!userId) {
        alert("Please login to modify your wishlist.");
        window.location.href = "/login";
        return;
    }

    $.ajax({
        url: "{{ url('/wishlist/store') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            _id: productId,
            name: productName,
            user_id: userId,
            user_email: userEmail,
            action: "remove"
        },
        success: function(response) {
            console.log(response.message);
            location.reload(); // refresh wishlist
        },
        error: function(xhr) {
            console.error(xhr.responseText);
        }
    });
}
</script>