<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Asset') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Validation Errors -->
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

                    <form action="{{ route('assets.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Column 1 -->
                            <div>
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700">Asset Name *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
				<!-- Critical Infrustructure -->
				<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox"
               name="is_critical_infrastructure"
               value="1"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
               {{ old('is_critical_infrastructure') ? 'checked' : '' }}>
        <span class="ml-2 text-sm text-gray-700">
            Critical Infrastructure (if out of service, affects parent asset)
        </span>
    </label>
</div>


                                <!-- Department -->
                                <div class="mt-4">
                                    <label for="department_id" class="block font-medium text-sm text-gray-700">Department *</label>
                                    <select name="department_id" id="department_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select a department...</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Category (Primary Tag Group) -->
                                <div class="mt-4">
                                    <label for="category_id" class="block font-medium text-sm text-gray-700">Primary Tag Group *</label>
                                    <select name="category_id" id="category_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select a tag group...</option>
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ old('category_id') == $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Tags (Multi-Select) -->
                                <div class="mt-4">
                                    <label for="tag_ids" class="block font-medium text-sm text-gray-700">Additional Tags</label>
                                    <select name="tag_ids[]" id="tag_ids" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tag_ids', [])) ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Parent Asset -->
                                <div class="mt-4">
                                    <label for="parent_asset_id" class="block font-medium text-sm text-gray-700">Parent Asset (Extrusion Line, Chiller System, etc.)</label>
                                    <select name="parent_asset_id" id="parent_asset_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">No Parent Asset</option>
                                        @foreach ($allAssets as $assetOption)
                                            <option value="{{ $assetOption->id }}" {{ old('parent_asset_id') == $assetOption->id ? 'selected' : '' }}>
                                                {{ $assetOption->name }} ({{ $assetOption->asset_tag_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Select the master equipment this component belongs to.</p>
                                </div>

                                <!-- Location -->
                                <div class="mt-4">
                                    <label for="location" class="block font-medium text-sm text-gray-700">Location</label>
                                    <input type="text" name="location" id="location" value="{{ old('location') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Manufacturer -->
                                <div class="mt-4">
                                    <label for="manufacturer" class="block font-medium text-sm text-gray-700">Manufacturer</label>
                                    <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Model Number -->
                                <div class="mt-4">
                                    <label for="model_number" class="block font-medium text-sm text-gray-700">Model Number</label>
                                    <input type="text" name="model_number" id="model_number" value="{{ old('model_number') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Serial Number -->
                                <div class="mt-4">
                                    <label for="serial_number" class="block font-medium text-sm text-gray-700">Serial Number</label>
                                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div>
                                <!-- Purchase Cost -->
                                <div>
                                    <label for="purchase_cost" class="block font-medium text-sm text-gray-700">Purchase Cost</label>
                                    <input type="number" name="purchase_cost" id="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" min="0"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- Purchase Date -->
                                <div class="mt-4">
                                    <label for="purchase_date" class="block font-medium text-sm text-gray-700">Purchase Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Warranty Expiration -->
                                <div class="mt-4">
                                    <label for="warranty_expiration_date" class="block font-medium text-sm text-gray-700">Warranty Expiration</label>
                                    <input type="date" name="warranty_expiration_date" id="warranty_expiration_date" value="{{ old('warranty_expiration_date') }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- PM Interval -->
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="pm_interval_value" class="block font-medium text-sm text-gray-700">PM Interval</label>
                                        <input type="number" name="pm_interval_value" id="pm_interval_value" value="{{ old('pm_interval_value') }}" min="1"
                                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div>
                                        <label for="pm_interval_unit" class="block font-medium text-sm text-gray-700">Unit</label>
                                        <select name="pm_interval_unit" id="pm_interval_unit" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            <option value="">Select unit...</option>
                                            <option value="days" {{ old('pm_interval_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                            <option value="weeks" {{ old('pm_interval_unit') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                                            <option value="months" {{ old('pm_interval_unit') == 'months' ? 'selected' : '' }}>Months</option>
                                            <option value="years" {{ old('pm_interval_unit') == 'years' ? 'selected' : '' }}>Years</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- PM Procedure -->
                                <div class="mt-4">
                                    <label for="pm_procedure_notes" class="block font-medium text-sm text-gray-700">PM Procedure Notes</label>
                                    <textarea name="pm_procedure_notes" id="pm_procedure_notes" rows="4"
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('pm_procedure_notes') }}</textarea>
                                </div>
                            </div>
                        </div> <!-- End Grid -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('assets.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Save Asset
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
