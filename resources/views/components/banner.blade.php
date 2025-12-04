<div class="flex flex-col md:flex-row items-center justify-between md:pl-20 py-14 md:py-0 bg-[#E6E9F2] my-16 rounded-xl overflow-hidden">

    {{-- Left Image --}}
    <img 
        class="max-w-56"
        src="{{ asset('assets/jbl_soundbox_image.png') }}" 
        alt="jbl_soundbox_image"
    >

    {{-- Center Content --}}
    <div class="flex flex-col items-center justify-center text-center space-y-2 px-4 md:px-0">
        <h2 class="text-2xl md:text-3xl font-semibold max-w-[290px]">
            Level Up Your Gaming Experience
        </h2>

        <p class="max-w-[343px] font-medium text-gray-800/60">
            From immersive sound to precise controls—everything you need to win
        </p>

        <a href="{{ route('collections.show', ['category' => 'All']) }}" 
           class="group flex items-center justify-center gap-1 px-12 py-2.5 bg-orange-600 rounded text-white">

            Buy now

            <img 
                class="group-hover:translate-x-1 transition" 
                src="{{ asset('assets/arrow_icon_white.svg') }}" 
                alt="arrow_icon_white"
            >
        </a>
    </div>

    {{-- Right Images (Desktop + Mobile) --}}
    <img 
        class="hidden md:block max-w-80"
        src="{{ asset('assets/md_controller_image.png') }}" 
        alt="md_controller_image"
    >

    <img 
        class="md:hidden"
        src="{{ asset('assets/sm_controller_image.png') }}" 
        alt="sm_controller_image"
    >

</div>
