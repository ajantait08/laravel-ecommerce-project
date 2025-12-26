{{-- <form method="GET" action="{{ route('products.index') }}" id="filterForm" class="space-y-6"> --}}

    <div class="flex justify-between items-center">
        <h3 class="font-semibold text-lg">Filters</h3>
        <a href="{{ route('all.products') }}" class="text-sm text-blue-600">Clear All</a>
    </div>

 {{-- Price Filter --}}
<div>
    <h4 class="font-medium mb-3">Price</h4>

    {{-- Price Labels --}}
    <div class="flex justify-between text-sm mb-3 text-gray-700">
        {{-- <span>₹<span id="priceMin">{{ request('min_price', $minPrice) }}</span></span>
        <span>₹<span id="priceMax">{{ request('max_price', $maxPrice) }}</span></span> --}}
        <span>₹<span id="priceMin">{{ request('min_price', $min_price) }}</span></span>
        <span>₹<span id="priceMax">{{ request('max_price', $max_price) }}</span></span>
    </div>

    {{-- Slider --}}
    <div id="priceSlider" class="mb-4"></div>

    {{-- Hidden Inputs --}}
    {{-- <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price', $minPrice) }}">
    <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price', $maxPrice) }}"> --}}

    <input type="hidden" name="min_price" id="min_price" value="{{ request('min_price',$min_price) }}">
    <input type="hidden" name="max_price" id="max_price" value="{{ request('max_price',$max_price) }}">
</div>

  {{-- Category --}}
<div class="mt-10">
    <h4 class="font-medium mb-3">Product Category</h4>

    @php
        $selectedCategories = request()->input('categories', []);
        if (!is_array($selectedCategories)) {
            $selectedCategories = [$selectedCategories];
        }
    @endphp

    @forelse ($category as $category)
        <label class="flex items-center gap-2 mb-2">
            <input type="checkbox" name="categories[]" value="{{ $category }}"
                {{ in_array($category, $selectedCategories) ? 'checked' : '' }}>
           {{ $category }}
        </label>
    @empty
        <p class="text-sm text-gray-500">
            No categories available (which usually means your controller query is broken).
        </p>
    @endforelse
    

    
</div>

{{-- Brand --}}
<div class="mt-10">
    <h4 class="font-medium mb-3">Product Brand</h4>

    @php
        $selectedBrand = request()->input('brand', []);
        if (!is_array($selectedBrand)) {
            $selectedBrand = [$selectedBrand];
        }
    @endphp

    @forelse ($brand as $brand)
        <label class="flex items-center gap-2 mb-2">
            <input type="checkbox" name="brand[]" value="{{ $brand }}"
                {{ in_array($brand, $selectedBrand) ? 'checked' : '' }}>
           {{ $brand }}
        </label>
    @empty
        <p class="text-sm text-gray-500">
            No brand available (which usually means your controller query is broken).
        </p>
    @endforelse
    

    
</div>


   {{-- Ratings --}}
<div class="mt-10">
    <h4 class="font-medium mb-3">Customer Ratings</h4>

    @php
        $selectedRatings = request()->input('ratings', []);
        if (!is_array($selectedRatings)) {
            $selectedRatings = [$selectedRatings];
        }
    @endphp

    <label class="flex items-center gap-2 mb-2">
        <input type="checkbox" name="ratings[]" value="4"
            {{ in_array(4, $selectedRatings) ? 'checked' : '' }}>
        4★ & above
    </label>

    <label class="flex items-center gap-2 mb-2">
        <input type="checkbox" name="ratings[]" value="3"
            {{ in_array(3, $selectedRatings) ? 'checked' : '' }}>
        3★ & above
    </label>

    <label class="flex items-center gap-2 mb-2">
        <input type="checkbox" name="ratings[]" value="2"
            {{ in_array(2, $selectedRatings) ? 'checked' : '' }}>
        2★ & above
    </label>

    <label class="flex items-center gap-2">
        <input type="checkbox" name="ratings[]" value="1"
            {{ in_array(1, $selectedRatings) ? 'checked' : '' }}>
        1★ & above
    </label>
</div>


