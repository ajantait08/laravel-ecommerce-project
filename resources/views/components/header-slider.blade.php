<div class="overflow-hidden relative w-full">

    {{-- Slides Wrapper --}}
    <div id="slider-track"
         class="flex transition-transform duration-700 ease-in-out"
         style="transform: translateX(0%);">

        @php
            $sliderData = [
                [
                    "id" => 1,
                    "title" => "Experience Pure Sound - Your Perfect Headphones Awaits!",
                    "offer" => "Limited Time Offer 30% Off",
                    "button1" => "Buy now",
                    "button2" => "Find more",
                    "img" => asset('assets/header_headphone_image.png'),
                    "category" => "Headphone"
                ],
                [
                    "id" => 2,
                    "title" => "Next-Level Gaming Starts Here - Discover PlayStation 5 Today!",
                    "offer" => "Hurry up only few lefts!",
                    "button1" => "Shop Now",
                    "button2" => "Explore Deals",
                    "img" => asset('assets/header_playstation_image.png'),
                    "category" => "Accessories"
                ],
                [
                    "id" => 3,
                    "title" => "Power Meets Elegance - Apple MacBook Pro is Here for you!",
                    "offer" => "Exclusive Deal 40% Off",
                    "button1" => "Order Now",
                    "button2" => "Learn More",
                    "img" => asset('assets/header_macbook_image.png'),
                    "category" => "Laptop"
                ],
            ];
        @endphp

        @foreach ($sliderData as $slide)
            <div class="flex flex-col-reverse md:flex-row items-center justify-between bg-[#E6E9F2] py-8 md:px-14 px-5 mt-6 rounded-xl min-w-full">

                <div class="md:pl-8 mt-10 md:mt-0">
                    <p class="md:text-base text-orange-600 pb-1">{{ $slide['offer'] }}</p>

                    <h1 class="max-w-lg md:text-[40px] md:leading-[48px] text-2xl font-semibold">
                        {{ $slide['title'] }}
                    </h1>

                    <div class="flex items-center mt-4 md:mt-6">
                        <a href="{{ url('collections/' . $slide['category']) }}"
                           class="md:px-10 px-7 md:py-2.5 py-2 bg-orange-600 rounded-full text-white font-medium">
                           {{ $slide['button1'] }}
                        </a>

                        <a href="{{ url('collections/' . $slide['category']) }}"
                           class="group flex items-center gap-2 px-6 py-2.5 font-medium">
                           {{ $slide['button2'] }}
                           <img class="group-hover:translate-x-1 transition"
                                src="{{ asset('assets/arrow_icon.svg') }}" alt="arrow_icon">
                        </a>
                    </div>
                </div>

                <div class="flex items-center flex-1 justify-center">
                    <img src="{{ $slide['img'] }}" class="md:w-72 w-48" alt="Slide">
                </div>

            </div>
        @endforeach

    </div>

    {{-- Dots --}}
    <div class="flex items-center justify-center gap-2 mt-8">
        @foreach ($sliderData as $index => $slide)
            <div class="h-2 w-2 rounded-full cursor-pointer slider-dot
                {{ $index === 0 ? 'bg-orange-600' : 'bg-gray-500/30' }}"
                data-index="{{ $index }}">
            </div>
        @endforeach
    </div>
</div>


{{-- Slider JavaScript --}}
<script>
    let currentSlide = 0;
    const totalSlides = {{ count($sliderData) }};
    const track = document.getElementById("slider-track");
    const dots = document.querySelectorAll(".slider-dot");

    // Auto Slide
    setInterval(() => {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    }, 3000);

    // Dot click event
    dots.forEach(dot => {
        dot.addEventListener("click", () => {
            currentSlide = parseInt(dot.dataset.index);
            updateSlider();
        });
    });

    // Update slide function
    function updateSlider() {
        track.style.transform = `translateX(-${currentSlide * 100}%)`;

        dots.forEach((d, i) => {
            d.classList.toggle("bg-orange-600", i === currentSlide);
            d.classList.toggle("bg-gray-500/30", i !== currentSlide);
        });
    }
</script>
