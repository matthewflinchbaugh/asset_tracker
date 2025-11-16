<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Asset') }}: {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-red-700">
                    <div class="font-semibold mb-2">Whoops! Something went wrong.</div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('assets.update', $asset->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" id="name" name="name"
                                       value="{{ old('name', $asset->name) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @php
                                        $statuses = ['active' => 'Active','inactive' => 'Inactive','retired' => 'Retired'];
                                    @endphp
                                    @foreach($statuses as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('status', $asset->status) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Temporarily Out of Service --}}
                            <div class="md:col-span-2">
                                <label class="inline-flex items-center mt-2">
                                    <input type="hidden" name="temporarily_out_of_service" value="0">
                                    <input type="checkbox"
                                           name="temporarily_out_of_service"
                                           value="1"
                                           class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                           {{ old('temporarily_out_of_service', $asset->temporarily_out_of_service) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        Mark asset temporarily out of service
                                    </span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500">
                                    This will also influence how the asset appears on the Kanban board.
                                </p>
                            </div>
				<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox"
               name="is_critical_infrastructure"
               value="1"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
               {{ old('is_critical_infrastructure', $asset->is_critical_infrastructure ?? false) ? 'checked' : '' }}>
        <span class="ml-2 text-sm text-gray-700">
            Critical Infrastructure (if out of service, affects parent asset)
        </span>
    </label>
</div>


                            {{-- Department --}}
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                <select id="department_id" name="department_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="" disabled {{ old('department_id', $asset->department_id) ? '' : 'selected' }}>
                                        Select a department…
                                    </option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ (string) old('department_id', $asset->department_id) === (string) $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Primary Tag Group / Category --}}
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">
                                    Primary Tag Group
                                </label>
                                <select id="category_id" name="category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="" disabled {{ old('category_id', $asset->category_id) ? '' : 'selected' }}>
                                        Select a primary tag group…
                                    </option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) old('category_id', $asset->category_id) === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tags --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Tags</label>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2">
                                @php
                                    $selectedTagIds = old('tag_ids', $asset->tags->pluck('id')->toArray());
                                @endphp
                                @foreach ($tags as $tag)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox"
                                               name="tag_ids[]"
                                               value="{{ $tag->id }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Parent Asset --}}
                        <div class="mt-6">
                            <label for="parent_asset_id" class="block text-sm font-medium text-gray-700">
                                Parent Asset (optional)
                            </label>
                            <select id="parent_asset_id" name="parent_asset_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">No parent asset</option>
                                @foreach ($availableChildren as $candidate)
                                    <option value="{{ $candidate->id }}"
                                        {{ (string) old('parent_asset_id', $asset->parent_asset_id) === (string) $candidate->id ? 'selected' : '' }}>
                                        {{ $candidate->name }} ({{ $candidate->asset_tag_id ?? 'No Tag' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Assign this asset as a component of another asset.
                            </p>
                        </div>

                        {{-- Child Assets --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Component Assets (children)</label>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                @php
                                    $currentChildIds = old('child_asset_ids', $asset->children->pluck('id')->toArray());
                                @endphp
                                @foreach ($availableChildren as $candidate)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox"
                                               name="child_asset_ids[]"
                                               value="{{ $candidate->id }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            {{ in_array($candidate->id, $currentChildIds) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ $candidate->name }} ({{ $candidate->asset_tag_id ?? 'No Tag' }})
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h3 class="text-md font-semibold text-gray-800 mb-4">Details</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                    <input type="text" id="location" name="location"
                                           value="{{ old('location', $asset->location) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                                    <input type="text" id="manufacturer" name="manufacturer"
                                           value="{{ old('manufacturer', $asset->manufacturer) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="model_number" class="block text-sm font-medium text-gray-700">Model Number</label>
                                    <input type="text" id="model_number" name="model_number"
                                           value="{{ old('model_number', $asset->model_number) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                                    <input type="text" id="serial_number" name="serial_number"
                                           value="{{ old('serial_number', $asset->serial_number) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="purchase_cost" class="block text-sm font-medium text-gray-700">Purchase Cost</label>
                                    <input type="number" step="0.01" id="purchase_cost" name="purchase_cost"
                                           value="{{ old('purchase_cost', $asset->purchase_cost) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                    <input type="date" id="purchase_date" name="purchase_date"
                                           value="{{ old('purchase_date', $asset->purchase_date) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="warranty_expiration_date" class="block text-sm font-medium text-gray-700">
                                        Warranty Expiration Date
                                    </label>
                                    <input type="date" id="warranty_expiration_date" name="warranty_expiration_date"
                                           value="{{ old('warranty_expiration_date', $asset->warranty_expiration_date) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                {{-- PM Interval --}}
                                <div>
                                    <label for="pm_interval_value" class="block text-sm font-medium text-gray-700">PM Interval</label>
                                    <div class="mt-1 flex space-x-2">
                                        <input type="number" min="1" id="pm_interval_value" name="pm_interval_value"
                                               value="{{ old('pm_interval_value', $asset->pm_interval_value) }}"
                                               class="block w-24 rounded-md border-gray-300 shadow-sm">
                                        <select id="pm_interval_unit" name="pm_interval_unit"
                                                class="block w-32 rounded-md border-gray-300 shadow-sm">
                                            @php
                                                $units = ['days' => 'Days', 'weeks' => 'Weeks', 'months' => 'Months', 'years' => 'Years'];
                                            @endphp
                                            <option value="">—</option>
                                            @foreach ($units as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ old('pm_interval_unit', $asset->pm_interval_unit) === $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Used to calculate the next PM due date when logging maintenance.
                                    </p>
                                </div>

                                {{-- Next PM Due --}}
                                <div>
                                    <label for="next_pm_due_date" class="block text-sm font-medium text-gray-700">
                                        Next PM Due
                                    </label>
                                    <input type="date" id="next_pm_due_date" name="next_pm_due_date"
                                           value="{{ old('next_pm_due_date', $asset->next_pm_due_date) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <p class="mt-1 text-xs text-gray-500">
                                        This is what drives the PM status colors on the Kanban board and children (if you’re syncing them).
                                    </p>
                                </div>
                            </div>

                            {{-- PM Procedure Notes --}}
                            <div class="mt-6">
                                <label for="pm_procedure_notes" class="block text-sm font-medium text-gray-700">
                                    PM Procedure Notes
                                </label>
                                <textarea id="pm_procedure_notes" name="pm_procedure_notes" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('pm_procedure_notes', $asset->pm_procedure_notes) }}</textarea>
                            </div>
                        </div>
			{{-- Checklist Templates --}}
<div class="mt-8 border-t border-gray-200 pt-6">
    <h3 class="text-md font-semibold text-gray-800 mb-4">PM Checklist Templates</h3>
    <p class="text-xs text-gray-500 mb-4">
        Add one or more PM checklist templates to this asset. You can use the same template multiple times
        for different components (e.g. "Motor #1", "Motor #2", "Heater A", etc.).
    </p>

    @php
        // If validation failed, repopulate from old() data.
        $oldAssignments = old('checklist_assignments');

        if (is_array($oldAssignments)) {
            $rows = collect($oldAssignments);
        } else {
            // Otherwise, build rows from existing pivot data
            $rows = $asset->checklistTemplates->map(function ($template) {
                return [
                    'template_id'    => $template->id,
                    'component_name' => $template->pivot->component_name,
                ];
            });
        }
    @endphp

    <div id="checklist-rows" class="space-y-3">
        @forelse ($rows as $index => $row)
            <div class="flex items-center space-x-3 checklist-row">
                {{-- Template selector --}}
                <select
                    name="checklist_assignments[{{ $index }}][template_id]"
                    class="mt-1 block w-1/3 rounded-md border-gray-300 shadow-sm text-sm"
                >
                    <option value="">-- Select template --</option>
                    @foreach($checklistTemplates as $template)
                        <option value="{{ $template->id }}"
                            {{ (int)($row['template_id'] ?? 0) === $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Component name --}}
                <input
                    type="text"
                    name="checklist_assignments[{{ $index }}][component_name]"
                    value="{{ $row['component_name'] ?? '' }}"
                    class="flex-1 rounded-md border-gray-300 shadow-sm text-sm"
                    placeholder="Component name (optional)"
                >

                {{-- Remove row --}}
                <button
                    type="button"
                    class="remove-checklist-row inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Remove
                </button>
            </div>
        @empty
            {{-- Default: one empty row --}}
            <div class="flex items-center space-x-3 checklist-row">
                <select
                    name="checklist_assignments[0][template_id]"
                    class="mt-1 block w-1/3 rounded-md border-gray-300 shadow-sm text-sm"
                >
                    <option value="">-- Select template --</option>
                    @foreach($checklistTemplates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>

                <input
                    type="text"
                    name="checklist_assignments[0][component_name]"
                    class="flex-1 rounded-md border-gray-300 shadow-sm text-sm"
                    placeholder="Component name (optional)"
                >

                <button
                    type="button"
                    class="remove-checklist-row inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Remove
                </button>
            </div>
        @endforelse
    </div>

    <button
        type="button"
        id="add-checklist-row"
        class="mt-3 inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
    >
        + Add PM Checklist
    </button>

    {{-- Template row used by JS when adding new rows --}}
    <template id="checklist-row-template">
        <div class="flex items-center space-x-3 checklist-row">
            <select
                name="checklist_assignments[__INDEX__][template_id]"
                class="mt-1 block w-1/3 rounded-md border-gray-300 shadow-sm text-sm"
            >
                <option value="">-- Select template --</option>
                @foreach($checklistTemplates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>

            <input
                type="text"
                name="checklist_assignments[__INDEX__][component_name]"
                class="flex-1 rounded-md border-gray-300 shadow-sm text-sm"
                placeholder="Component name (optional)"
            >

            <button
                type="button"
                class="remove-checklist-row inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                Remove
            </button>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('checklist-rows');
            const addBtn    = document.getElementById('add-checklist-row');
            const template  = document.getElementById('checklist-row-template');

            if (!container || !addBtn || !template) return;

            let nextIndex = container.querySelectorAll('.checklist-row').length;

            addBtn.addEventListener('click', function () {
                let html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
                container.insertAdjacentHTML('beforeend', html);
                nextIndex++;
            });

            container.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-checklist-row')) {
                    const row = event.target.closest('.checklist-row');
                    if (row) {
                        row.remove();
                    }
                }
            });
        });
    </script>
</div>



                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('assets.show', $asset->id) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-semibold text-white hover:bg-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

