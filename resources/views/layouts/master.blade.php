<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Website')</title>

    {{-- Tailwind / CSS --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons (FontAwesome CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100">

    {{-- Include Navbar here if you want globally --}}
    {{-- @include('components.navbar') --}}

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    {{-- @include('components.footer') --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleWishlist(productId, productName) {
            const userId = "{{ session('user.id') }}";
            const userEmail = "{{ session('user.email') }}";
            
            // console.log("userId:", userId);
            // console.log("userEmail:", userEmail);
            
            
            if (!userId) {
                alert("Please login to manage wishlist");
                window.location.href = "/login";
                return;
            }
        
            // Determine action (add/remove)
            const isWishlisted = document.querySelector(`#wish-icon-${productId}`).dataset.wished === "true";
            const action = isWishlisted ? "remove" : "add";
        
            $.ajax({
                url: "{{ url('/wishlist/store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    _id: productId,
                    name: productName,
                    user_id: userId,
                    user_email: userEmail,
                    action: action
                },
                success: function(response) {
                    console.log(response.message);
        
                    // Toggle icon UI
                    const icon = document.querySelector(`#wish-icon-${productId}`);
                    if (action === "add") {
                        icon.src = "{{ asset('assets/heart_filled_icon.svg') }}";
                        icon.dataset.wished = "true";
                    } else {
                        icon.src = "{{ asset('assets/heart_icon.svg') }}";
                        icon.dataset.wished = "false";
                    }
                },
                error: function(xhr) {
                    console.error("Wishlist error", xhr.responseText);
                }
            });
        }
        </script>

<script>

    const CART_ADD_URL = "{{ url('/cart/add') }}";
    const CSRF = "{{ csrf_token() }}";
    const userId = "{{ session('user.id') }}";
    const CART_REMOVE_URL = "{{ url('/cart/remove') }}";
    const CART_INC_URL = "{{ url('/cart/increment') }}";
    const CART_DEC_URL = "{{ url('/cart/decrement') }}";
    const CART_CONTENTS_URL = "{{ url('/cart/contents') }}";

    // open/close
    function openCart() {
        document.getElementById('cart-overlay').classList.remove('hidden');
        document.getElementById('slide-cart').classList.remove('translate-x-full');
        //loadCart();
    }
    function closeCart() {
        document.getElementById('cart-overlay').classList.add('hidden');
        document.getElementById('slide-cart').classList.add('translate-x-full');
    }

    // scroll featured
    function scrollFeatured(px) {
        document.getElementById('featured-track').scrollBy({ left: px, behavior: 'smooth' });
    }

    // load cart via AJAX
    async function loadCart() {
        // if (!userId) {
        //     alert("Please login to add items to cart");
        //     window.location.href = "/login";
        //     return;
        // }
        
        const res = await fetch(CART_CONTENTS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({user_id: userId})
        });
        //const res = await fetch(CART_CONTENTS_URL);
        if (!res.ok) return;
        const data = await res.json();

        //console.log("Cart data:", data);

        const itemsEl = document.getElementById('cart-items');
        itemsEl.innerHTML = '';

        if (!data || data.length === 0) {
            document.getElementById('cart-empty-msg').style.display = 'block';
            document.getElementById('cart-subtotal').innerText = '₹0.00';
            document.getElementById('cart-total').innerText = '₹0.00';
            document.getElementById('checkout-btn').disabled = true;
            return;
        } else {
            document.getElementById('cart-empty-msg').style.display = 'none';
            document.getElementById('checkout-btn').disabled = false;
        }

        let subtotal = 0;
        data.forEach(item => {
            subtotal += (item.price * item.quantity);

            const itemHtml = document.createElement('div');
            itemHtml.className = 'flex items-center justify-between mb-3 border-b pb-3';
            itemHtml.innerHTML = `
                <div class="flex items-center space-x-3">
                    ${item.image ? `<img src="${item.image}" class="w-16 h-16 object-cover rounded">` : `<div class="w-16 h-16 bg-gray-200 rounded"></div>`}
                    <span class="font-medium w-36">${escapeHtml(item.name)}</span>
                </div>

                <div class="flex flex-col items-end space-y-2">
                    <div class="text-right">
                        <span class="font-semibold">₹${(item.price * item.quantity).toFixed(2)}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="decrementQuantity('${item.product_id}',${item.quantity})" class="px-2 py-1 bg-gray-200 rounded">-</button>
                        <span id="qty-${item.product_id}">${item.quantity}</span>
                        <button onclick="incrementQuantity('${item.product_id}',${item.quantity})" class="px-2 py-1 bg-gray-200 rounded">+</button>
                    </div>

                    <button onclick="removeFromCart('${item.product_id}')" class="text-red-500 text-sm">Remove</button>
                </div>
            `;
            itemsEl.appendChild(itemHtml);
        });

        // totals
        document.getElementById('cart-subtotal').innerText = '₹' + subtotal.toFixed(2);
        document.getElementById('cart-total').innerText = '₹' + subtotal.toFixed(2);
    }

    // helpers
    function escapeHtml(unsafe) {
        return unsafe
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

        async function addToCart(productId, qty = 1) {
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(";").shift();
            return null;
        }

        let guestId = getCookie("guest_cart_id");

        const res = await fetch(CART_ADD_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CSRF
            },
            body: JSON.stringify({
                product_id: productId,
                qty,
                user_id: userId ?? "",   // logged-in user
                cart_id: guestId ?? ""   // guest cart cookie
            })
        });

        const data = await res.json();

        updateCartCount();
        await loadCart();
        openCart();
    }


    async function removeFromCart(productId) {
        // if (!userId) {
        //     alert("Please login to add items to cart");
        //     window.location.href = "/login";
        //     return;
        // }
        //console.log("Entered Remove From Cart !");
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(";").shift();
            return null;
        }

        let guestId = getCookie("guest_cart_id");
        const res = await fetch(CART_REMOVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ product_id: productId , user_id : userId , cart_id : guestId ?? ""})
        });
        updateCartCount();
        await loadCart();
    }

    function updateCartCount() {
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }

    let guestId = getCookie("guest_cart_id");
    $.ajax({
        url: "/cart/count",
        type: "POST",
        data : {
            _token : "{{ csrf_token()}}",
            //cart_id : guestId
        },
        success: function (res) {
            console.log("Cart count:", res.count);
            document.getElementById("cart-items-count").classList.remove("hidden");
            document.getElementById("cart-items-count").classList.add("block");
            $("#cart-items-count").text(res.count);
        }
    });
    }

    async function incrementQuantity(productId,qty) {
        const reqQty = qty + 1;
        const res = await fetch(CART_INC_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ product_id: productId , quantity: reqQty , user_id : userId })
        });
        const data = await res.json();
        document.getElementById('qty-' + productId).innerText = reqQty;
        loadCart();
    }

    async function decrementQuantity(productId,qty) {
        if (qty <= 1) {
            // Remove item if qty is 0 or less
            await removeFromCart(productId);
            return;            
        }
        const reqQty = qty - 1;
        const res = await fetch(CART_DEC_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ product_id: productId , quantity: reqQty , user_id : userId})
        });
        const data = await res.json();
        if (!data.error) {
            document.getElementById('qty-' + productId).innerText = reqQty;
        }
        loadCart();
    }

    // Hook checkout button
    document.getElementById('checkout-btn').addEventListener('click', function () {
        // if you have a checkout route
        if (!userId) {
            alert("Please login to proceed to checkout");
            window.location.href = "/login";
            return;
        }
        window.location.href = '/checkout';
    });

    // Expose openCart globally so you can call it from navbar toggle button
    window.openCart = openCart;
    window.closeCart = closeCart;

    // Load cart once on script load if you'd like
    // loadCart();
</script>
        
</body>
</html>
