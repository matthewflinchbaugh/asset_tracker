<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Webhook') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-4 text-sm text-red-600">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('webhooks.store') }}">
                        @csrf

                        {{-- Target URL --}}
                        <div class="mb-4">
                            <label for="url" class="block text-sm font-medium text-gray-700">
                                Target URL
                            </label>
                            <input
                                id="url"
                                type="url"
                                name="url"
                                value="{{ old('url') }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="https://example.com/webhook/endpoint"
                            >
                        </div>

                        {{-- Event Type --}}
                        <div class="mb-4">
                            <label for="event_type" class="block text-sm font-medium text-gray-700">
                                Trigger Event
                            </label>
                            <select
                                id="event_type"
                                name="event_type"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="" disabled {{ old('event_type') ? '' : 'selected' }}>
                                    -- Select an event --
                                </option>
                                @foreach($eventTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('event_type') === $key ? 'selected' : '' }}>
                                        {{ $label }} ({{ $key }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                These are the event types used by your code (e.g. LOG_ADDED, ASSET_OOS, ASSET_RETURNED_TO_SERVICE, ASSET_MAINTENANCE_DUE).
                            </p>
                        </div>

                        {{-- Fields to include --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Payload Fields
                                </label>

                                <div class="space-x-2">
                                    <button
                                        id="select-all-fields"
                                        type="button"
                                        class="inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Select all
                                    </button>
                                    <button
                                        id="clear-all-fields"
                                        type="button"
                                        class="inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Clear all
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mb-2">
                                Choose which fields will be included in the webhook payload. If you select nothing,
                                your code will fall back to sending all available fields.
                            </p>

                            <div
                                id="fields-checkboxes"
                                class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-auto border border-gray-200 rounded-md p-2 bg-gray-50"
                            >
                                @php
                                    $selectedFields = old('fields', []);
                                @endphp

                                @foreach($fields as $key => $label)
                                    <label class="inline-flex items-center text-sm text-gray-700">
                                        <input
                                            type="checkbox"
                                            name="fields[]"
                                            value="{{ $key }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            {{ in_array($key, $selectedFields, true) ? 'checked' : '' }}
                                        >
                                        <span class="ml-2">
                                            {{ $label }}
                                            <span class="text-xs text-gray-400">({{ $key }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Active toggle --}}
                        <div class="mb-6 flex items-center">
                            <input
                                id="is_active"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ old('is_active', true) ? 'checked' : '' }}
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Webhook is active
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a
                                href="{{ route('webhooks.index') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Create Webhook
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- Simple vanilla JS for Select all / Clear all --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllBtn = document.getElementById('select-all-fields');
            const clearAllBtn = document.getElementById('clear-all-fields');
            const container = document.getElementById('fields-checkboxes');

            if (!container) return;

            const getCheckboxes = () => Array.from(container.querySelectorAll('input[type="checkbox"]'));

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    getCheckboxes().forEach(cb => cb.checked = true);
                });
            }

            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    getCheckboxes().forEach(cb => cb.checked = false);
                });
            }
        });
    </script>
</x-app-layout>

