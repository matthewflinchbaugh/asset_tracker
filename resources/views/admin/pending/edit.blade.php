<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approve Pending Asset') }}: {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pending.update', $asset->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Column 1 -->
                            <div>
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block font-medium text-sm text-gray-700">Asset Name *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $asset->name) }}" required 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- Department (Read-only) -->
                                <div class="mt-4">
                                    <label for="department_id" class="block font-medium text-sm text-gray-700">Department (Cannot be changed)</label>
                                    <input type="text" value="{{ $asset->department->name }}" disabled 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 bg-gray-100">
                                </div>

                                <!-- Tag -->
                                <div class="mt-4">
                                    <label for="category_id" class="block font-medium text-sm text-gray-700">Tag *</label>
                                    <select name="category_id" id="category_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select a category...</option>
                                        @foreach ($tags as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location -->
                                <div class="mt-4">
                                    <label for="location" class="block font-medium text-sm text-gray-700">Location</label>
                                    <input type="text" name="location" id="location" value="{{ old('location', $asset->location) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Manufacturer -->
                                <div class="mt-4">
                                    <label for="manufacturer" class="block font-medium text-sm text-gray-700">Manufacturer</label>
                                    <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer', $asset->manufacturer) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Model Number -->
                                <div class="mt-4">
                                    <label for="model_number" class="block font-medium text-sm text-gray-700">Model Number</label>
                                    <input type="text" name="model_number" id="model_number" value="{{ old('model_number', $asset->model_number) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Serial Number -->
                                <div class="mt-4">
                                    <label for="serial_number" class="block font-medium text-sm text-gray-700">Serial Number</label>
                                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div>
                                <!-- Purchase Cost (Admin Only) -->
                                <div>
                                    <label for="purchase_cost" class="block font-medium text-sm text-gray-700">Purchase Cost *</label>
                                    <input type="number" name="purchase_cost" id="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" step="0.01" min="0" required
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="text-xs text-gray-500 mt-1">Admin must set the final purchase cost before approval.</p>
                                </div>

                                <!-- Purchase Date -->
                                <div class="mt-4">
                                    <label for="purchase_date" class="block font-medium text-sm text-gray-700">Purchase Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $asset->purchase_date) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                
                                <!-- Warranty Expiration -->
                                <div class="mt-4">
                                    <label for="warranty_expiration_date" class="block font-medium text-sm text-gray-700">Warranty Expiration</label>
                                    <input type="date" name="warranty_expiration_date" id="warranty_expiration_date" value="{{ old('warranty_expiration_date', $asset->warranty_expiration_date) }}"
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>

                                <!-- PM Interval -->
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="pm_interval_value" class="block font-medium text-sm text-gray-700">PM Interval</label>
                                        <input type="number" name="pm_interval_value" id="pm_interval_value" value="{{ old('pm_interval_value', $asset->pm_interval_value) }}" min="1"
                                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    </div>
                                    <div>
                                        <label for="pm_interval_unit" class="block font-medium text-sm text-gray-700">Unit</label>
                                        <select name="pm_interval_unit" id="pm_interval_unit" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                            <option value="">Select unit...</option>
                                            <option value="days" {{ old('pm_interval_unit', $asset->pm_interval_unit) == 'days' ? 'selected' : '' }}>Days</option>
                                            <option value="weeks" {{ old('pm_interval_unit', $asset->pm_interval_unit) == 'weeks' ? 'selected' : '' }}>Weeks</option>
                                            <option value="months" {{ old('pm_interval_unit', $asset->pm_interval_unit) == 'months' ? 'selected' : '' }}>Months</option>
                                            <option value="years" {{ old('pm_interval_unit', $asset->pm_interval_unit) == 'years' ? 'selected' : '' }}>Years</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- PM Procedure -->
                                <div class="mt-4">
                                    <label for="pm_procedure_notes" class="block font-medium text-sm text-gray-700">PM Procedure Notes</label>
                                    <textarea name="pm_procedure_notes" id="pm_procedure_notes" rows="3"
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('pm_procedure_notes', $asset->pm_procedure_notes) }}</textarea>
                                </div>
                                
                                <!-- Commissioning Notes (Read-only) -->
                                <div class="mt-4">
                                    <label for="commissioning_notes" class="block font-medium text-sm text-gray-700">Technician's Commissioning Notes</label>
                                    <textarea name="commissioning_notes" id="commissioning_notes" rows="3"
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('commissioning_notes', $asset->commissioning_notes) }}</textarea>
                                </div>
                            </div>
                        </div> <!-- End Grid -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('pending.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                Approve Asset
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
