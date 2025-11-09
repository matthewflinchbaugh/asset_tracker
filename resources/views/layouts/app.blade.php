<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Printed Solid Asset Tracker') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
	    <main>

            @if (session('success'))
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            {{ $slot }}
        </main>
        </div>
n    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('keydown', function (e) {
                // We only care about the Enter key
                if (e.key === 'Enter' || e.keyCode === 13) {
                    
                    const target = e.target;
                    
                    // We only want to intercept 'Enter' on standard text-like inputs.
                    // We EXCLUDE 'textarea' (where Enter means new line) and all buttons.
                    if (target.tagName === 'INPUT' && target.type !== 'submit' && target.type !== 'button' && target.type !== 'reset') {
                        
                        const form = target.closest('form');
                        if (!form) return;
                        
                        e.preventDefault(); // Stop the form from submitting

                        // Get all focusable elements in the form, in order
                        const inputs = Array.from(
                            form.querySelectorAll('input, select, textarea')
                        ).filter(el => 
                            !el.disabled && 
                            !el.hidden && 
                            el.type !== 'hidden' &&
                            window.getComputedStyle(el).display !== 'none' &&
                            el.tabIndex !== -1
                        );

                        const currentIndex = inputs.indexOf(target);
                        const nextIndex = currentIndex + 1;

                        if (nextIndex < inputs.length) {
                            // Focus the next element in the form
                            inputs[nextIndex].focus();
                        } else {
                            // If at the end, focus the form's primary submit button
                            const submitButton = form.querySelector('button[type="submit"]');
                            if (submitButton) {
                                submitButton.focus();
                            }
                        }
                    }
                }
            });
        });
    <\/script>

    </body>
</html>
