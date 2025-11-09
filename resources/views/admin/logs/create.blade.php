<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $log->exists ? 'Edit Maintenance Log' : 'New Maintenance Log' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Logging work for: <span class="font-bold">{{ $asset->name }} ({{ $asset->asset_tag_id }})</span>
                    </h3>
                    
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

                    <!-- Determine route: store new or update existing draft -->
                    @if ($log->exists)
                        <form action="{{ route('logs.draft.update', $log->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                    @else
                        <form action="{{ route('assets.logs.store', $asset->id) }}" method="POST" enctype="multipart/form-data">
                    @endif
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Column 1 -->
                            <div>
                                <!-- Event Type -->
                                <div>
                                    <label for="event_type" class="block font-medium text-sm text-gray-700">Event Type *</label>
                                    <select name="event_type" id="event_type" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select an event type...</option>
                                        <option value="commissioning" {{ old('event_type', $log->event_type) == 'commissioning' ? 'selected' : '' }}>Commissioning</option>
                                        <option value="scheduled_maintenance" {{ old('event_type', $log->event_type) == 'scheduled_maintenance' ? 'selected' : '' }}>Scheduled Maintenance</option>
                                        <option value="unscheduled_repair" {{ old('event_type', $log->event_type) == 'unscheduled_repair' ? 'selected' : '' }}>Unscheduled Repair</option>
                                        <option value="inspection" {{ old('event_type', $log->event_type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                        <option value="decommissioning" {{ old('event_type', $log->event_type) == 'decommissioning' ? 'selected' : '' }}>Decommissioning</option>
                                    </select>
                                </div>

                                <!-- Service Date -->
                                <div class="mt-4">
                                    <label for="service_date" class="block font-medium text-sm text-gray-700">Service Date *</label>
                                    <input type="datetime-local" name="service_date" id="service_date" 
                                           value="{{ old('service_date', $log->service_date ? \Carbon\Carbon::parse($log->service_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" 
                                           required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- Parts Cost -->
                                <div class="mt-4">
                                    <label for="parts_cost" class="block font-medium text-sm text-gray-700">Parts Cost ($)</label>
                                    <input type="number" name="parts_cost" id="parts_cost" value="{{ old('parts_cost', $log->parts_cost ?? 0.00) }}" step="0.01" min="0"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- Labor Hours -->
                                <div class="mt-4">
                                    <label for="labor_hours" class="block font-medium text-sm text-gray-700">Labor Hours</label>
                                    <input type="number" name="labor_hours" id="labor_hours" value="{{ old('labor_hours', $log->labor_hours ?? 0.00) }}" step="0.25" min="0"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                            
                            <!-- Column 2 -->
                            <div>
                                <!-- Description -->
                                <div>
                                    <label for="description_of_work" class="block font-medium text-sm text-gray-700">Description of Work Performed *</label>
                                    <textarea name="description_of_work" id="description_of_work" rows="7" required
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description_of_work', $log->description_of_work) }}</textarea>
                                </div>
                                
                                <!-- File Attachments -->
                                <div class="mt-4">
                                    <label for="attachments" class="block font-medium text-sm text-gray-700">Attachments (Max 5 files, 5MB each)</label>
                                    <input type="file" name="attachments[]" id="attachments" multiple 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                
                                <!-- Existing Attachments (if editing draft) -->
                                @if ($log->exists && $log->attachments->isNotEmpty())
                                <div class="mt-4">
                                    <span class="block font-medium text-sm text-gray-700">Current Attachments:</span>
                                    <ul class="list-disc list-inside text-sm text-gray-600">
                                        @foreach($log->attachments as $attachment)
                                            <li>{{ $attachment->original_file_name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div> <!-- End Grid -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6 space-x-4">
                            <a href="{{ route('assets.show', $asset->id) }}" class="text-gray-600 hover:text-gray-900">
                                Cancel
                            </a>
                            
                            <!-- Save Draft Button -->
                            <button type="submit" name="save_draft" value="1" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400">
                                Save Draft
                            </button>
                            
                            <!-- Submit Button -->
                            <button type="submit" name="submit_log" value="1" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Submit Log
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
