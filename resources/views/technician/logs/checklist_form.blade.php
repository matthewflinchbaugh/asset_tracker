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

                    <h3 class="text-lg font-medium text-gray-900">
                        Logging work for: <span class="font-bold">{{ $asset->name }} ({{ $asset->asset_tag_id }})</span>
                    </h3>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">
                        Checklist: <span class="text-indigo-600">{{ $template->name }}</span>
                    </h4>
                    
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

                    @if ($log->exists)
                        <form action="{{ route('logs.draft.update', $log->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                    @else
                        <form action="{{ route('assets.logs.store', $asset->id) }}" method="POST" enctype="multipart/form-data">
                    @endif
                        @csrf
                        <input type="hidden" name="is_checklist_form" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Column 1: Standard Fields -->
                            <div>
                                <!-- Event Type -->
                                <div>
                                    <label for="event_type" class="block font-medium text-sm text-gray-700">Event Type *</label>
                                    <select name="event_type" id="event_type" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="scheduled_maintenance" selected>Scheduled Maintenance</option>
                                        <option value="unscheduled_repair" {{ old('event_type', $log->event_type) == 'unscheduled_repair' ? 'selected' : '' }}>Unscheduled Repair</option>
                                        <option value="inspection" {{ old('event_type', $log->event_type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    </select>
                                </div>
				<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox"
               name="mark_asset_out_of_service"
               value="1"
               class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
               {{ old('mark_asset_out_of_service') || ($asset->temporarily_out_of_service ?? false) ? 'checked' : '' }}>
        <span class="ml-2 text-sm text-gray-700">
            Mark asset temporarily out of service
        </span>
    </label>
    <p class="text-xs text-gray-500 mt-1">
        When checked, this asset will be highlighted as "Out of Service" on the Kanban board.
    </p>
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
                                
                                <!-- File Attachments -->
                                <div class="mt-4">
                                    <label for="attachments" class="block font-medium text-sm text-gray-700">Attachments</label>
                                    <input type="file" name="attachments[]" id="attachments" multiple 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                            
                            <!-- Column 2: Dynamic Checklist Fields -->
                            <div>
                                <h4 class="font-medium text-sm text-gray-700 mb-2">Checklist Fields</h4>
                                <div class="space-y-4">
                                    @foreach ($template->fields as $field)
                                        <div>
                                            <label for="checklist_data_{{ $field->id }}" class="block font-medium text-sm text-gray-700">{{ $field->label }}</label>
                                            
                                            @if ($field->field_type == 'numeric')
                                                <input type="number" step="0.01" name="checklist_data[{{ $field->id }}]" id="checklist_data_{{ $field->id }}"
                                                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            
                                            @elseif ($field->field_type == 'text')
                                                <input type="text" name="checklist_data[{{ $field->id }}]" id="checklist_data_{{ $field->id }}"
                                                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            
                                            @elseif ($field->field_type == 'pass_fail')
                                                <select name="checklist_data[{{ $field->id }}]" id="checklist_data_{{ $field->id }}"
                                                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                                    <option value="Pass">Pass</option>
                                                    <option value="Fail">Fail</option>
                                                    <option value="N/A">N/A</option>
                                                </select>
                                            
                                            @elseif ($field->field_type == 'checkbox')
                                                <input type="checkbox" name="checklist_data[{{ $field->id }}]" id="checklist_data_{{ $field->id }}"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    <label for="description_of_work" class="block font-medium text-sm text-gray-700">General Notes</label>
                                    <textarea name="description_of_work" id="description_of_work" rows="3"
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description_of_work', $log->description_of_work) }}</textarea>
                                </div>
                            </div>
                        </div> <!-- End Grid -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6 space-x-4">
                            <a href="{{ route('assets.show', $asset->id) }}" class="text-gray-600 hover:text-gray-900">
                                Cancel
                            </a>
                            <button type="submit" name="save_draft" value="1" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400">
                                Save Draft
                            </button>
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
