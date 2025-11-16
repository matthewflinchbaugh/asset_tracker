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
                        Fill out as much information as you know. This request will be submitted for approval.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('technician.assets.store') }}" method="POST">
                        @csrf

                        {{-- Basic info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Name *</label>
                                <input type="text" name="name"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('name') }}" required>
                            </div>
                        {{-- Critical Infrustructure --}}
				<div class="mt-2">
    <label class="inline-flex items-center">
        <input type="checkbox"
               name="is_critical_infrastructure"
               value="1"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
               {{ old('is_critical_infrastructure') ? 'checked' : '' }}>
        <span class="ml-2 text-sm text-gray-700">
            Critical Infrastructure (if this equipment is out of service,
            it affects the parent asset)
        </span>
    </label>
</div>


                            <div>
                                <label class="block font-medium text-sm text-gray-700">Department *</label>
                                <select name="department_id"
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select a department...</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">
                                    Primary Tag Group *
                                </label>
                                <select name="category_id"
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select a tag group...</option>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}" {{ old('category_id') == $tag->id ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Location</label>
                                <input type="text" name="location"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('location') }}">
                            </div>
                        </div>

                        {{-- Specification details --}}
                        <div class="mt-6 border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Manufacturer</label>
                                <input type="text" name="manufacturer"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('manufacturer') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Model Number</label>
                                <input type="text" name="model_number"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('model_number') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Serial Number</label>
                                <input type="text" name="serial_number"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('serial_number') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Purchase Cost</label>
                                <input type="number" step="0.01" name="purchase_cost"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('purchase_cost') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Purchase Date</label>
                                <input type="date" name="purchase_date"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('purchase_date') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Warranty Expiration</label>
                                <input type="date" name="warranty_expiration_date"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('warranty_expiration_date') }}">
                            </div>
                        </div>

                        {{-- PM info --}}
                        <div class="mt-6 border-t pt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">PM Interval</label>
                                <input type="number" min="1" name="pm_interval_value"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('pm_interval_value') }}">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Interval Unit</label>
                                <select name="pm_interval_unit"
                                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select...</option>
                                    @foreach (['days','weeks','months','years'] as $unit)
                                        <option value="{{ $unit }}" {{ old('pm_interval_unit') === $unit ? 'selected' : '' }}>
                                            {{ ucfirst($unit) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Next PM Due Date</label>
                                <input type="date" name="next_pm_due_date"
                                       class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                       value="{{ old('next_pm_due_date') }}">
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">PM Procedure Notes</label>
                                <textarea name="pm_procedure_notes" rows="3"
                                          class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">{{ old('pm_procedure_notes') }}</textarea>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Commissioning / Comments</label>
                                <textarea name="commissioning_notes" rows="3"
                                          class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">{{ old('commissioning_notes') }}</textarea>
                            </div>
                        </div>

                        {{-- PM Checklists --}}
                        <div class="mt-8 border-t pt-4">
                            <h4 class="text-sm font-semibold text-gray-800 mb-2">
                                PM Checklists that apply
                            </h4>
                            <p class="text-xs text-gray-500 mb-3">
                                (Optional) Select one or more PM checklist templates and, if needed, specify the component name.
                            </p>

                            @for ($i = 0; $i < 3; $i++)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Checklist Template</label>
                                        <select name="checklist_assignments[{{ $i }}][template_id]"
                                                class="block w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                            <option value="">-- None --</option>
                                            @foreach ($checklistTemplates as $template)
                                                <option value="{{ $template->id }}"
                                                    {{ old("checklist_assignments.$i.template_id") == $template->id ? 'selected' : '' }}>
                                                    {{ $template->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Component Name (optional)</label>
                                        <input type="text"
                                               name="checklist_assignments[{{ $i }}][component_name]"
                                               class="block w-full mt-1 rounded-md border-gray-300 shadow-sm"
                                               value="{{ old("checklist_assignments.$i.component_name") }}">
                                    </div>
                                </div>
                            @endfor
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Submit for Approval
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

