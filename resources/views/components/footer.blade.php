<footer>
    <div class="flex flex-col md:flex-row items-start justify-center px-6 md:px-16 lg:px-32 gap-10 py-14 border-b border-gray-500/30 text-gray-500">
        
        {{-- Logo + Description --}}
        <div class="w-4/5">
            <img class="w-28 md:w-32" src="{{ asset('assets/logo.svg') }}" alt="logo">
            <p class="mt-6 text-sm">
                Lorem Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard dummy text
                ever since the 1500s, when an unknown printer took a galley of type
                and scrambled it to make a type specimen book.
            </p>
        </div>

        {{-- Company Links --}}
        <div class="w-1/2 flex items-center justify-start md:justify-center">
            <div>
                <h2 class="font-medium text-gray-900 mb-5">Company</h2>
                <ul class="text-sm space-y-2">
                    <li>
                        <a class="hover:underline transition" href="#">Home</a>
                    </li>
                    <li>
                        <a class="hover:underline transition" href="#">About us</a>
                    </li>
                    <li>
                        <a class="hover:underline transition" href="#">Contact us</a>
                    </li>
                    <li>
                        <a class="hover:underline transition" href="#">Privacy policy</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Contact Section (Optional - commented like React version) --}}
        {{--
        <div class="w-1/2 flex items-start justify-start md:justify-center">
            <div>
                <h2 class="font-medium text-gray-900 mb-5">Get in touch</h2>
                <div class="text-sm space-y-2">
                    <p>+1-234-567-890</p>
                    <p>contact@greatstack.dev</p>
                </div>
            </div>
        </div>
        --}}
    </div>

    {{--
    <p class="py-4 text-center text-xs md:text-sm">
        Copyright 2025 © GreatStack.dev All Right Reserved.
    </p>
    --}}
</footer>
