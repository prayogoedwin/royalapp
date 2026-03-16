<!-- Footer -->
<footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
    <div class="px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400">
            <div class="mb-2 md:mb-0">
                &copy; {{ date('Y') }} 
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ config('app.name') }}</span>
                <span class="mx-2">•</span>
                <span>v{{ config('app.version', '1.0.0') }}</span>
            </div>
            <div>
                {{ __('Developed by') }} 
                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ config('app.developer', 'Your Company') }}</span>
            </div>
        </div>
    </div>
</footer>
