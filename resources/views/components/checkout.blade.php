<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Checkout</title>
  <script src="https://js.stripe.com/v3/"></script>
  {{-- <script defer>
    window.Laravel = {
      csrfToken: "{{ csrf_token() }}",
      stripePublicKey: "{{ config('services.stripe.public') ?? env('STRIPE_PUBLIC') }}"
    };
  </script> --}}
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js" defer></script>
  {{-- <script defer>
  // fetch wrapper with csrf
  axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
  </script> --}}

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white text-gray-800">
  {{-- include your Navbar if needed --}}
  <div class="max-w-5xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
    {{-- Billing Form --}}
    <div>
      <form id="billing-form" class="space-y-4" novalidate>
        <h2 class="text-xl font-bold mb-4">Billing details</h2>
        <div>
          <label class="block font-medium">Email address *</label>
          <input type="email" id="email" name="email" class="w-full border rounded px-3 py-2" />
          <p id="error-email" class="text-red-500 text-sm hidden"></p>
        </div>

        <div>
          <label class="block font-medium">Phone no. *</label>
          <input type="text" id="phone" name="phone" class="w-full border rounded px-3 py-2" />
          <p id="error-phone" class="text-red-500 text-sm hidden"></p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-medium">First name *</label>
            <input type="text" id="firstName" name="firstName" class="w-full border rounded px-3 py-2" />
            <p id="error-firstName" class="text-red-500 text-sm hidden"></p>
          </div>
          <div>
            <label class="block font-medium">Last name *</label>
            <input type="text" id="lastName" name="lastName" class="w-full border rounded px-3 py-2" />
            <p id="error-lastName" class="text-red-500 text-sm hidden"></p>
          </div>
        </div>

        {{-- Address Autocomplete --}}
        <div id="address-autocomplete" class="relative">
          <label class="block font-medium">Street address *</label>
          <input type="text" id="street" name="street" class="w-full border rounded px-3 py-2 mb-2" placeholder="Search location" autocomplete="off" />
          <ul id="address-suggestions" class="absolute z-10 bg-white border w-full rounded shadow max-h-48 overflow-y-auto hidden"></ul>
          <p id="error-street" class="text-red-500 text-sm hidden"></p>

          <input type="text" id="apartment" name="apartment" class="w-full border rounded px-3 py-2 mt-4" placeholder="Apartment, suite, unit, etc. (optional)" />
        </div>

        <div>
          <label class="block font-medium">Country / Region *</label>
          <select id="country" name="country" class="w-full border rounded px-3 py-2">
            <option value="">Select country</option>
            <option value="India">India</option>
            <option value="USA">United States</option>
            <option value="UK">United Kingdom</option>
            <option value="Canada">Canada</option>
          </select>
          <p id="error-country" class="text-red-500 text-sm hidden"></p>
        </div>

        <div>
          <label class="block font-medium">Town / City *</label>
          <input type="text" id="city" name="city" class="w-full border rounded px-3 py-2" />
          <p id="error-city" class="text-red-500 text-sm hidden"></p>
        </div>

        <div>
          <label class="block font-medium">State *</label>
          <input type="text" id="state" name="state" class="w-full border rounded px-3 py-2" />
          <p id="error-state" class="text-red-500 text-sm hidden"></p>
        </div>

        <div>
          <label class="block font-medium">Pincode *</label>
          <input type="text" id="pincode" name="pincode" class="w-full border rounded px-3 py-2" />
          <p id="error-pincode" class="text-red-500 text-sm hidden"></p>
        </div>
      </form>
    </div>

    {{-- Order Summary --}}
    <div class="bg-gray-50 p-6 rounded-lg shadow space-y-3">
      <h2 class="text-xl font-bold mb-4">Order Details</h2>

      <div id="order-items" class="divide-y space-y-2">
        @if(count($cartitems ?? []) > 0)
          @foreach($cartitems as $item)
            <div data-product-id="{{ $item->product_id }}" class="flex justify-between items-center py-3">
              <div class="flex items-center gap-3">
                <img src="{{ $item->image ?? ''}}" class="w-12 h-12 rounded object-cover border" />
                <div>
                  <p class="font-medium">{{ $item->name }}</p>
                  <p class="text-sm text-gray-500">₹{{ number_format($item->price,2) }}</p>
                  <div class="flex items-center gap-2 mt-2">
                    <button class="decrement px-2 py-1 border rounded font-bold">−</button>
                    <span class="px-2 text-sm qty">{{ $item->quantity }}</span>
                    <button class="increment px-2 py-1 border rounded">+</button>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                <button class="remove text-red-500 text-xs mt-1 hover:underline">Remove</button>
              </div>
            </div>
          @endforeach
        @else
          <div class="py-10 text-center text-gray-600">Your cart is empty</div>
        @endif
      </div>

      {{-- coupon box placeholder --}}
      {{-- <div id="coupon-box" class="mt-4">
        <label class="block font-medium">Coupon</label>
        <input type="text" id="coupon-code" class="w-full border rounded px-3 py-2" placeholder="Apply coupon" />
        <button id="apply-coupon" class="mt-2 px-3 py-2 bg-blue-600 text-white rounded">Apply</button>
        <p id="coupon-msg" class="text-sm mt-1"></p>
      </div> --}}

      {{-- Price summary --}}
      <div class="flex justify-between">
        <span>Subtotal:</span>
        <span id="subtotal">₹0.00</span>
      </div>

      {{-- <div id="discount-row" class="hidden flex justify-between text-green-600">
        <span>Discount:</span>
        <span id="discount-amount">- ₹0.00</span>
      </div>

      <div class="space-y-2">
        <p class="font-medium">Shipping:</p>
        <label class="flex items-center space-x-2">
          <input type="radio" name="shipping" value="free" checked />
          <span>Free Shipping (₹0)</span>
        </label>

        <label class="flex items-center space-x-2">
          <input type="radio" name="shipping" value="expedited" />
          <span>Expedited Shipping (₹199)</span>
        </label>
      </div>

      <div class="flex justify-between">
        <span>Shipping Cost:</span>
        <span id="shipping-cost">₹0</span>
      </div> --}}

      <div class="flex justify-between font-semibold text-lg border-t pt-2 mt-2">
        <span>Total:</span>
        <span id="final-total">₹0.00</span>
      </div>

      {{-- Stripe placeholder / will be injected by JS --}}
      <div id="stripe-container" class="mt-4"></div>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Basic data built from server DOM
  function parsePrice(s) {
    return Number(s.replace(/[^0-9.-]+/g,""));
  }

  // Build cart items array from DOM
  function getCartArrayFromDOM() {
    const items = [];
    document.querySelectorAll('#order-items > div[data-product-id]').forEach(div => {
      const pid = div.getAttribute('data-product-id');
      const name = div.querySelector('p.font-medium').innerText;
      const priceText = div.querySelector('p.text-sm').innerText.replace('₹','');
      const price = parseFloat(priceText);
      const qty = parseInt(div.querySelector('.qty').innerText) || 1;
      items.push({ product_id: pid, quantity: qty, price, name });
    });
    return items;
  }

  function recalcTotals() {
    const cart = getCartArrayFromDOM();
    const subtotalVal = cart.reduce((acc,i) => acc + (i.price * i.quantity), 0);
    document.getElementById('subtotal').innerText = '₹' + subtotalVal.toFixed(2);
    document.getElementById('final-total').innerText = '₹' + subtotalVal.toFixed(2);

    const shippingRadio = document.querySelector('input[name="shipping"]:checked').value;
    const shippingCost = shippingRadio === 'expedited' ? 199 : 0;
    document.getElementById('shipping-cost').innerText = '₹' + shippingCost;

    const discount = window.checkoutDiscount || 0;
    if (discount > 0) {
      document.getElementById('discount-row').classList.remove('hidden');
      document.getElementById('discount-amount').innerText = '- ₹' + discount.toFixed(2);
    } else {
      document.getElementById('discount-row').classList.add('hidden');
    }

    const total = Math.max(subtotalVal - discount + shippingCost, 0);
    // Uncomment Later
    //document.getElementById('final-total').innerText = '₹' + total.toFixed(2);
    // Uncomment Later
    return {subtotal: subtotalVal, shippingCost, discount, total};
  }

  // initialize totals
  recalcTotals();

  // Increment / decrement / remove handlers (call API endpoints)
  document.querySelectorAll('.increment').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const pid = e.target.closest('[data-product-id]').dataset.productId;
      await axios.post('/api/cart/increment', { product_id: pid });
      const qtySpan = e.target.closest('[data-product-id]').querySelector('.qty');
      qtySpan.innerText = parseInt(qtySpan.innerText) + 1;
      recalcTotals();
    });
  });

  document.querySelectorAll('.decrement').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const container = e.target.closest('[data-product-id]');
      const pid = container.dataset.productId;
      const qtySpan = container.querySelector('.qty');
      let qty = parseInt(qtySpan.innerText);
      if (qty <= 1) return;
      await axios.post('/api/cart/decrement', { product_id: pid });
      qtySpan.innerText = qty - 1;
      recalcTotals();
    });
  });

  document.querySelectorAll('.remove').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const container = e.target.closest('[data-product-id]');
      const pid = container.dataset.productId;
      await axios.post('/api/cart/remove', { product_id: pid });
      container.remove();
      recalcTotals();
    });
  });

  // shipping change
  document.querySelectorAll('input[name="shipping"]').forEach(r => {
    r.addEventListener('change', recalcTotals);
  });

  // simple coupon flow (demo)
  window.checkoutDiscount = 0;
  document.getElementById('apply-coupon').addEventListener('click', function() {
    const code = document.getElementById('coupon-code').value.trim();
    if (!code) { document.getElementById('coupon-msg').innerText = 'Enter coupon'; return; }
    // Demo: coupon 'SAVE50' gives 50 INR off
    if (code === 'SAVE50') {
      window.checkoutDiscount = 50;
      document.getElementById('coupon-msg').innerText = 'Coupon applied: -₹50';
    } else {
      window.checkoutDiscount = 0;
      document.getElementById('coupon-msg').innerText = 'Invalid coupon';
    }
    recalcTotals();
  });

  // Address autocomplete using Nominatim
  const streetInput = document.getElementById('street');
  const suggestionsEl = document.getElementById('address-suggestions');
  let debounceTimer;
  streetInput.addEventListener('input', function(e) {
    const q = e.target.value;
    clearTimeout(debounceTimer);
    if (!q) { suggestionsEl.classList.add('hidden'); return; }
    debounceTimer = setTimeout(async () => {
      try {
        const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=' + encodeURIComponent(q));
        const data = await res.json();
        suggestionsEl.innerHTML = '';
        data.slice(0,5).forEach(item => {
          const li = document.createElement('li');
          li.className = 'p-2 hover:bg-gray-100 cursor-pointer text-sm';
          li.innerText = item.display_name;
          li.addEventListener('click', () => {
            const addr = item.address || {};
            document.getElementById('street').value = item.display_name || '';
            document.getElementById('city').value = addr.city || addr.town || addr.village || '';
            document.getElementById('state').value = addr.state || '';
            document.getElementById('country').value = addr.country || '';
            document.getElementById('pincode').value = addr.postcode || '';
            suggestionsEl.classList.add('hidden');
          });
          suggestionsEl.appendChild(li);
        });
        suggestionsEl.classList.remove('hidden');
      } catch(err) { console.error(err); }
    }, 300);
  });

  // Stripe integration: create payment intent when user clicks Pay
  let stripe = Stripe(window.Laravel.stripePublicKey);
  let elements;

  async function createAndMountPaymentElement(clientSecret) {
    const options = { clientSecret };
    elements = stripe.elements(options);
    const paymentElement = elements.create('payment');
    const container = document.getElementById('stripe-container');
    container.innerHTML = '<form id="stripe-payment-form" class="mt-6 space-y-4"><div id="payment-element"></div><p id="stripe-error" class="text-red-500"></p><button id="pay-btn" class="w-full py-2 rounded-lg font-semibold bg-green-600 text-white">Pay Now</button></form>';
    paymentElement.mount('#payment-element');

    document.getElementById('stripe-payment-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const payBtn = document.getElementById('pay-btn');
      payBtn.disabled = true;
      // validate form fields client-side
      if (!validateForm()) { 
      document.getElementById('stripe-error').innerText = 'Please fix form errors'; payBtn.disabled = false; return; }

      // Save preliminary order to backend (before confirm)
      const totals = recalcTotals();
      const formData = gatherFormData();
      try {
        const saveRes = await axios.post('/api/save-order-details', {
          form: formData,
          cart_items: getCartArrayFromDOM(),
          total_amount: totals.total,
          shipping_cost: totals.shippingCost,
          payment_intent_id: '',
          payment_status: '',
          applied_coupon: window.checkoutDiscount ? { code: document.getElementById('coupon-code').value, discount: window.checkoutDiscount } : null,
          user_id: null,
          user_email: formData.email || null
        });
        const user_info_id = saveRes.data.user_info_id;
        localStorage.setItem('user_info_id', user_info_id);
      } catch (err) {
        console.error('Error saving order', err);
      }

      // submit payment
      const {error} = await elements.submit();
      if (error) {
        document.getElementById('stripe-error').innerText = error.message || 'Payment error';
        payBtn.disabled = false;
        return;
      }

      const confirm = await stripe.confirmPayment({
        elements,
        confirmParams: {
          return_url: window.location.origin + '/payment-success?amount=' + Math.round(totals.total) + '&user_info_id=' + btoa(localStorage.getItem('user_info_id') || '')
        },
      });

      if (confirm.error) {
        document.getElementById('stripe-error').innerText = confirm.error.message || 'Payment failed';
        payBtn.disabled = false;
      }
    });
  }

  function validateForm() {
    let ok = true;
    // simple validators
    const phone = document.getElementById('phone').value.trim();
    if (!/^\d{10}$/.test(phone)) { 
    document.getElementById('error-phone').classList.remove('hidden'); 
    document.getElementById('error-phone').innerText = 'Invalid phone'; 
    ok = false; 
    } 
    else { 
    document.getElementById('error-phone').classList.add('hidden'); 
  }
    const firstName = document.getElementById('firstName').value.trim();
    if (!firstName) { document.getElementById('error-firstName').classList.remove('hidden'); document.getElementById('error-firstName').innerText='Required'; ok = false; } else { document.getElementById('error-firstName').classList.add('hidden'); }
    const lastName = document.getElementById('lastName').value.trim();
    if (!lastName) { document.getElementById('error-lastName').classList.remove('hidden'); document.getElementById('error-lastName').innerText='Required'; ok = false; } else { document.getElementById('error-lastName').classList.add('hidden'); }
    const street = document.getElementById('street').value.trim();
    if (!street) { document.getElementById('error-street').classList.remove('hidden'); document.getElementById('error-street').innerText='Required'; ok = false; } else { document.getElementById('error-street').classList.add('hidden'); }
    const country = document.getElementById('country').value.trim();
    if (!country) { document.getElementById('error-country').classList.remove('hidden'); document.getElementById('error-country').innerText='Required'; ok = false; } else { document.getElementById('error-country').classList.add('hidden'); }
    const city = document.getElementById('city').value.trim();
    if (!city) { document.getElementById('error-city').classList.remove('hidden'); document.getElementById('error-city').innerText='Required'; ok = false; } else { document.getElementById('error-city').classList.add('hidden'); }
    const state = document.getElementById('state').value.trim();
    if (!state) { document.getElementById('error-state').classList.remove('hidden'); document.getElementById('error-state').innerText='Required'; ok = false; } else { document.getElementById('error-state').classList.add('hidden'); }
    const pincode = document.getElementById('pincode').value.trim();
    if (!/^\d{6}$/.test(pincode)) { document.getElementById('error-pincode').classList.remove('hidden'); document.getElementById('error-pincode').innerText='Invalid pincode'; ok = false; } else { document.getElementById('error-pincode').classList.add('hidden'); }
    return ok;
  }

  function gatherFormData() {
    return {
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      firstName: document.getElementById('firstName').value.trim(),
      lastName: document.getElementById('lastName').value.trim(),
      country: document.getElementById('country').value.trim(),
      street: document.getElementById('street').value.trim(),
      apartment: document.getElementById('apartment').value.trim(),
      city: document.getElementById('city').value.trim(),
      state: document.getElementById('state').value.trim(),
      pincode: document.getElementById('pincode').value.trim(),
      notes: '',
    };
  }

  // When user clicks any button to start payment creation: we will create payment intent on demand
  // Instead: show a "Start payment" button in stripe container (if not created)
  const stripeContainer = document.getElementById('stripe-container');
  const startBtn = document.createElement('button');
  startBtn.className = 'w-full py-2 rounded-lg font-semibold bg-green-600 text-white';
  startBtn.innerText = 'Proceed to Payment';
  startBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!validateForm()) return;
    const totals = recalcTotals();
    const amountPaise = Math.round(totals.total * 100);
    startBtn.disabled = true;
    try {
      const res = await axios.post('/api/create-payment-intent', { amount: amountPaise, billing: gatherFormData() });
      const clientSecret = res.data.clientSecret;
      await createAndMountPaymentElement(clientSecret);
    } catch (err) {
      console.error('create payment intent error', err);
      alert('Could not start payment. Please try again.');
      startBtn.disabled = false;
    }
  });
  stripeContainer.appendChild(startBtn);
});
</script>
</body>
</html>
