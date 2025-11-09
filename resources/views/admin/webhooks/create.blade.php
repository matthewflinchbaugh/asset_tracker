<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Webhook') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <strong>Whoops! Something went wrong.</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('webhooks.store') }}" method="POST" x-data="{ 
                        selectedFields: {}, 
                        fieldsList: @js(array_keys($fields)),
                        allFields: @js($fields),
                        payloadPreview: '{}'
                    }" x-init="
                        selectedFields = @js(array_fill_keys(array_keys($fields), false));
                        updatePayload = () => {
                            let payload = {};
                            fieldsList.forEach(key => {
                                if (selectedFields[key]) {
                                    payload[key] = allFields[key]; // Use field label as mock data
                                }
                            });
                            payloadPreview = JSON.stringify(payload, null, 2);
                        };
                        $watch('selectedFields', updatePayload);
                        updatePayload();
                    ">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Column 1: Core Config -->
                            <div class="md:col-span-1 space-y-4">
                                <h4 class="font-semibold text-md border-b pb-2">Webhook Endpoint</h4>
                                
                                <!-- URL -->
                                <div>
                                    <label for="url" class="block font-medium text-sm text-gray-700">Target URL *</label>
                                    <input type="url" name="url" id="url" value="{{ old('url') }}" required
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    <p class="text-xs text-gray-500 mt-1">This is where the JSON payload will be POSTed.</p>
                                </div>

                                <!-- Event Type -->
                                <div>
                                    <label for="event_type" class="block font-medium text-sm text-gray-700">Trigger Event *</label>
                                    <select name="event_type" id="event_type" required 
                                            class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select event...</option>
                                        @foreach ($eventTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('event_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Column 2: Field Selector -->
                            <div class="md:col-span-1 space-y-4">
                                <h4 class="font-semibold text-md border-b pb-2">Fields to Include in Payload</h4>
                                <div class="grid grid-cols-2 gap-2 max-h-96 overflow-y-auto border p-3 rounded-md">
                                    @foreach ($fields as $key => $label)
                                        <div class="flex items-center">
                                            <input type="checkbox" name="fields[]" id="field_{{ $key }}" value="{{ $key }}"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                   x-model="selectedFields['{{ $key }}']">
                                            <label for="field_{{ $key }}" class="ml-2 text-sm text-gray-700">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Column 3: Live Preview -->
                            <div class="md:col-span-1 space-y-4">
                                <h4 class="font-semibold text-md border-b pb-2">Live JSON Preview</h4>
                                <pre class="bg-gray-800 text-green-300 p-4 rounded-md overflow-x-auto text-xs" 
                                     x-text="payloadPreview"></pre>
                                <p class="text-xs text-gray-500">The preview uses field labels as placeholder data.</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('webhooks.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Save Webhook
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
