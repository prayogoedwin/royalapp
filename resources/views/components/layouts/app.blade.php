<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    
    <!-- Dynamic Favicon -->
    @php
        $appName = config('app.name', 'App');
        $initials = collect(explode(' ', $appName))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(3)
            ->implode('');
    @endphp
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,
        %3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E
            %3Crect width='100' height='100' rx='20' fill='%232563eb'/%3E
            %3Ctext x='50' y='50' text-anchor='middle' dy='0.35em' font-family='Arial, sans-serif' font-size='45' font-weight='bold' fill='white'%3E{{ $initials }}%3C/text%3E
        %3C/svg%3E">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- FontAwesome CDN for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        sidebar: {
                            DEFAULT: '#ffffff',
                            foreground: '#1f2937'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        .sidebar-transition { transition: width 0.3s ease; }
        .content-transition { transition: margin-left 0.3s ease; }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
        .dark .bg-sidebar { background-color: #1f2937; }
        .dark .text-sidebar-foreground { color: #f3f4f6; }
    </style>
    
    <script>
        window.setAppearance = function(appearance) {
            let setDark = () => document.documentElement.classList.add('dark')
            let setLight = () => document.documentElement.classList.remove('dark')
            let setButtons = (appearance) => {
                document.querySelectorAll('button[onclick^="setAppearance"]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(appearance === button.value))
                })
            }
            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')
                window.localStorage.removeItem('appearance')
                media.matches ? setDark() : setLight()
            } else if (appearance === 'dark') {
                window.localStorage.setItem('appearance', 'dark')
                setDark()
            } else if (appearance === 'light') {
                window.localStorage.setItem('appearance', 'light')
                setLight()
            }
            if (document.readyState === 'complete') {
                setButtons(appearance)
            } else {
                document.addEventListener("DOMContentLoaded", () => setButtons(appearance))
            }
        }
        window.setAppearance(
            "{{ auth()->user()->theme_preference ?? '' }}" || 
            window.localStorage.getItem('appearance') || 
            'system'
        )
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') === null ? window.innerWidth >= 1024 : (localStorage.getItem('sidebarOpen') === 'true' && window.innerWidth >= 1024),
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarOpen', this.sidebarOpen);
    },
    temporarilyOpenSidebar() {
        if (!this.sidebarOpen) {
            this.sidebarOpen = true;
            localStorage.setItem('sidebarOpen', true);
        }
    },
    formSubmitted: false,
}">

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col">

        <x-layouts.app.header />

        <!-- Main Content Area -->
        <div class="flex flex-1 overflow-hidden">

            <x-layouts.app.sidebar />

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-auto bg-gray-100 dark:bg-gray-900 content-transition">
                <div class="flex-1 p-6">
                    <!-- Success Message -->
                    @session('status')
                        <div x-data="{ showStatusMessage: true }" x-show="showStatusMessage"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-md">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500 dark:text-green-400"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 dark:text-green-200">{{ session('status') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <div class="-mx-1.5 -my-1.5">
                                        <button @click="showStatusMessage = false"
                                            class="inline-flex rounded-md p-1.5 text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <span class="sr-only">{{ __('Dismiss') }}</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endsession

                    {{ $slot }}

                </div>
                
                <x-layouts.app.footer />
            </main>
        </div>
    </div>
</body>

</html>
