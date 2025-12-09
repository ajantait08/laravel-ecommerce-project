@php
$products = [
    [
        'id' => 1,
        'image' => asset('assets/girl_with_headphone_image.png'),
        'title' => 'Unparalleled Sound',
        'description' => 'Experience crystal-clear audio with premium headphones.',
        'category' => 'Headphone',
    ],
    [
        'id' => 2,
        'image' => asset('assets/girl_with_earphone_image.png'),
        'title' => 'Stay Connected',
        'description' => 'Compact and stylish earphones for every occasion.',
        'category' => 'Earphone',
    ],
    [
        'id' => 3,
        'image' => asset('assets/boy_with_laptop_image.png'),
        'title' => 'Power in Every Pixel',
        'description' => 'Shop the latest laptops for work, gaming, and more.',
        'category' => 'Laptop',
    ],
];
@endphp

<div class="mt-14">

    {{-- Title --}}
    <div class="flex flex-col items-center">
        <p class="text-3xl font-medium">Featured Products</p>
        <div class="w-28 h-0.5 bg-orange-600 mt-2"></div>
    </div>

    {{-- Product Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-14 mt-12 md:px-14 px-4">

        @foreach($products as $product)
            <div class="relative group">

                {{-- Image --}}
                <img src="{{ $product['image'] }}" 
                     alt="{{ $product['title'] }}"
                     class="group-hover:brightness-75 transition duration-300 w-full h-auto object-cover">

                {{-- Overlay Content --}}
                <div class="group-hover:-translate-y-4 transition duration-300 absolute bottom-8 left-8 text-white space-y-2">

                    <p class="font-medium text-xl lg:text-2xl">
                        {{ $product['title'] }}
                    </p>

                    <p class="text-sm lg:text-base leading-5 max-w-60">
                        {{ $product['description'] }}
                    </p>

                    <a href="{{ url('/collections/' . $product['category']) }}"
                       class="flex items-center gap-1.5 bg-orange-600 px-4 py-2 rounded">

                        Buy now

                        <img src="{{ asset('assets/redirect_icon.svg') }}" 
                             class="h-3 w-3"
                             alt="Redirect Icon">

                    </a>
                </div>
            </div>
        @endforeach

    </div>

    <div class="mt-16 flex flex-col items-center">
        <a href="{{ url('/all-featured-products') }}"
           class="px-12 py-2.5 border rounded text-gray-500/70 hover:bg-slate-50/90 transition">
            See more
        </a>
    </div>

    

</div>
