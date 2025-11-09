<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit New Equipment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        New Equipment Details
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Fill out all known information for the new asset. An admin will review this submission, add the purchase cost, and approve it.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('technician.assets.store') }}" method="POST">
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

                                <!-- Department -->
                                <div class="mt-4">
                                    <label for="department_id" class="block font-medium text-sm text-gray-700">Department *</label>
                                    <select name="department_id" id="department_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select a department...</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }} ({{ $department->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tag -->
                                <div class="mt-4">
                                    <label for="category_id" class="block font-medium text-sm text-gray-700">Tag *</label>
                                    <select name="category_id" id="category_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                        <option value="">Select a category...</option>
                                        @foreach ($tags as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                <!-- Purchase Date -->
                                <div>
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
                                
                                <!-- Commissioning Notes -->
                                <div class="mt-4">
                                    <label for="commissioning_notes" class="block font-medium text-sm text-gray-700">Commissioning Notes (This will become the first log entry)</label>
                                    <textarea name="commissioning_notes" id="commissioning_notes" rows="6"
                                              class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('commissioning_notes') }}</textarea>
                                </div>
                            </div>
                        </div> <!-- End Grid -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Submit for Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
