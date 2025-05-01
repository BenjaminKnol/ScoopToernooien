<x-layouts.app.header>
    @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-4">
            @auth
                <a
                    href="{{ url('/dashboard') }}"
                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                >
                    Dashboard
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                >
                    Log in
                </a>
            @endauth
        </nav>
    @endif
</x-layouts.app.header>
