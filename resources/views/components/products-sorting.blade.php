@php
    $min = request('min_price');
    $max = request('max_price');
    $rating = request('rating');
    //exit;
@endphp

<div class="flex items-center gap-4 border-b pb-3">

    <span class="font-medium">Sort By</span>

    @php
        $sort = request('sort');
    @endphp

    {{-- <a href="{{ request()->fullUrlWithQuery(['sort' => 'popularity']) }}"
       class="{{ $sort == 'popularity' || !$sort ? 'text-blue-600 font-semibold' : '' }}">
        Popularity
    </a> --}}

    <a href="{{ route('all.products', [
            'min_price' => $min,
            'max_price' => $max,
            'rating' => $rating,
            'sort' => 'price_low_high'
        ]) }}"
        class="{{ $sort == 'price_low_high' ? 'text-blue-600 font-semibold' : '' }}" >
        Price — Low to High
    </a>

    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_high_low']) }}"
       class="{{ $sort == 'price_high_low' ? 'text-blue-600 font-semibold' : '' }}">
        Price — High to Low
    </a>

    {{-- <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
       class="{{ $sort == 'newest' ? 'text-blue-600 font-semibold' : '' }}">
        Newest First
    </a>

    <a href="{{ request()->fullUrlWithQuery(['sort' => 'discount']) }}"
       class="{{ $sort == 'discount' ? 'text-blue-600 font-semibold' : '' }}">
        Discount
    </a> --}}

</div>
