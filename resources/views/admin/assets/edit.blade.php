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
                <div class="p-6">
                    <form method="POST" action="{{ route('assets.update', $asset->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                    @php $statuses = ['active' => 'Active','inactive' => 'Inactive','retired' => 'Retired']; @endphp
                                    @foreach($statuses as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status',$asset->status)===$val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Department --}}
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                <select id="department_id" name="department_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="" disabled @selected(!old('department_id', $asset->department_id))>Select a department…</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            @selected((int)old('department_id', $asset->department_id) === (int)$department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Category --}}
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                <select id="category_id" name="category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="" disabled @selected(!old('category_id', $asset->category_id))>Select a category…</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @selected((int)old('category_id', $asset->category_id) === (int)$category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Parent Asset --}}
                            <div>
                                <label for="parent_asset_id" class="block text-sm font-medium text-gray-700">Parent Asset</label>
                                <select id="parent_asset_id" name="parent_asset_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">(None)</option>
                                    @foreach ($availableChildren as $assetOption)
                                        <option value="{{ $assetOption->id }}"
                                            @selected((int)old('parent_asset_id', $asset->parent_asset_id) === (int)$assetOption->id)>
                                            {{ $assetOption->name }} @if($assetOption->asset_tag_id) ({{ $assetOption->asset_tag_id }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Location --}}
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" id="location" name="location"
                                       value="{{ old('location', $asset->location) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Manufacturer --}}
                            <div>
                                <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                                <input type="text" id="manufacturer" name="manufacturer"
                                       value="{{ old('manufacturer', $asset->manufacturer) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Model Number --}}
                            <div>
                                <label for="model_number" class="block text-sm font-medium text-gray-700">Model Number</label>
                                <input type="text" id="model_number" name="model_number"
                                       value="{{ old('model_number', $asset->model_number) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Serial Number --}}
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                                <input type="text" id="serial_number" name="serial_number"
                                       value="{{ old('serial_number', $asset->serial_number) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Documentation Link --}}
                            <div class="md:col-span-2">
                                <label for="documentation_link" class="block text-sm font-medium text-gray-700">Documentation Link</label>
                                <input type="url" id="documentation_link" name="documentation_link"
                                       value="{{ old('documentation_link', $asset->documentation_link) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Purchase Cost --}}
                            <div>
                                <label for="purchase_cost" class="block text-sm font-medium text-gray-700">Purchase Cost</label>
                                <input type="number" step="0.01" id="purchase_cost" name="purchase_cost"
                                       value="{{ old('purchase_cost', $asset->purchase_cost) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Purchase Date --}}
                            <div>
                                <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                <input type="date" id="purchase_date" name="purchase_date"
                                       value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- Warranty Expiration --}}
                            <div>
                                <label for="warranty_expiration_date" class="block text-sm font-medium text-gray-700">Warranty Expiration</label>
                                <input type="date" id="warranty_expiration_date" name="warranty_expiration_date"
                                       value="{{ old('warranty_expiration_date', $asset->warranty_expiration_date?->format('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            {{-- PM Interval --}}
                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-1">
                                    <label for="pm_interval_value" class="block text-sm font-medium text-gray-700">PM Interval</label>
                                    <input type="number" min="1" id="pm_interval_value" name="pm_interval_value"
                                           value="{{ old('pm_interval_value', $asset->pm_interval_value) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="col-span-2">
                                    <label for="pm_interval_unit" class="block text-sm font-medium text-gray-700">&nbsp;</label>
                                    <select id="pm_interval_unit" name="pm_interval_unit"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        @php $units = ['days'=>'Days','weeks'=>'Weeks','months'=>'Months','years'=>'Years']; @endphp
                                        <option value="">(None)</option>
                                        @foreach($units as $val=>$label)
                                            <option value="{{ $val }}" @selected(old('pm_interval_unit', $asset->pm_interval_unit)===$val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Next PM Due --}}
                            <div>
                                <label for="next_pm_due_date" class="block text-sm font-medium text-gray-700">Next PM Due</label>
                                <input type="date" id="next_pm_due_date" name="next_pm_due_date"
                                       value="{{ old('next_pm_due_date', $asset->next_pm_due_date?->format('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>

                        {{-- Procedure Notes --}}
                        <div class="mt-6">
                            <label for="pm_procedure_notes" class="block text-sm font-medium text-gray-700">PM Procedure Notes</label>
                            <textarea id="pm_procedure_notes" name="pm_procedure_notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('pm_procedure_notes', $asset->pm_procedure_notes) }}</textarea>
                        </div>

                        {{-- Commissioning Notes --}}
                        <div class="mt-6">
                            <label for="commissioning_notes" class="block text-sm font-medium text-gray-700">Commissioning Notes</label>
                            <textarea id="commissioning_notes" name="commissioning_notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('commissioning_notes', $asset->commissioning_notes) }}</textarea>
                        </div>

                        {{-- Additional Tags --}}
                        <div class="mt-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Additional Tags</h3>
                            </div>
                            <div class="mt-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @php $selectedTagIds = old('tag_ids', $asset->tags->pluck('id')->all()); @endphp
                                @foreach ($tags as $tag)
                                    <label class="inline-flex items-center space-x-2 rounded-md border p-2">
                                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                               @checked(collect($selectedTagIds)->contains($tag->id))>
                                        <span class="text-sm">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Child Assets --}}
                        <div class="mt-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Child Assets</h3>
                            </div>
                            @php $selectedChildren = old('child_asset_ids', $asset->children->pluck('id')->all()); @endphp
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($availableChildren as $child)
                                    <label class="flex items-center space-x-2 rounded-md border p-2">
                                        <input type="checkbox" name="child_asset_ids[]" value="{{ $child->id }}"
                                               @checked(collect($selectedChildren)->contains($child->id))>
                                        <span class="text-sm">{{ $child->name }} @if($child->asset_tag_id) ({{ $child->asset_tag_id }}) @endif</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- PM Checklist Assignments --}}
                        <div class="mt-10">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">PM Checklist Assignments</h3>
                                <button id="add-assignment"
                                        type="button"
                                        class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
                                        onclick="addAssignmentRow()">
                                    + Add PM Template
                                </button>
                            </div>

                            <div id="assignments" class="mt-3 space-y-3">
                                @php
                                    $oldAssignments = collect(old('checklist_assignments', []));
                                    $initial = $oldAssignments->isNotEmpty()
                                        ? $oldAssignments
                                        : $asset->checklistTemplates->map(fn($t) => [
                                            'template_id' => $t->id,
                                            'component_name' => $t->pivot->component_name ?? '',
                                        ]);
                                @endphp

                                @forelse ($initial as $idx => $assignment)
                                    <div class="assignment-row grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Template</label>
                                            <select name="checklist_assignments[{{ $idx }}][template_id]"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                                <option value="">(None)</option>
                                                @foreach ($checklistTemplates as $template)
                                                    <option value="{{ $template->id }}"
                                                        @selected((int)($assignment['template_id'] ?? $assignment->template_id ?? 0) === (int)$template->id)>
                                                        {{ $template->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Component Name (optional)</label>
                                            <input type="text"
                                                   name="checklist_assignments[{{ $idx }}][component_name]"
                                                   value="{{ $assignment['component_name'] ?? $assignment->component_name ?? '' }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div class="md:pt-6">
                                            <button type="button"
                                                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                                    onclick="removeAssignmentRow(this)">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    {{-- Start with one empty row --}}
                                    <div class="assignment-row grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Template</label>
                                            <select name="checklist_assignments[0][template_id]"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                                <option value="">(None)</option>
                                                @foreach ($checklistTemplates as $template)
                                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Component Name (optional)</label>
                                            <input type="text" name="checklist_assignments[0][component_name]"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div class="md:pt-6">
                                            <button type="button"
                                                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                                    onclick="removeAssignmentRow(this)">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-10 flex flex-wrap justify-end gap-3">
                            <a href="{{ route('assets.show', $asset->id) }}"
                               class="inline-flex items-center rounded-md border px-4 py-2 text-sm hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Update Asset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Simple JS for dynamic PM rows --}}
    <script>
        function nextIndex() {
            const container = document.getElementById('assignments');
            return container.querySelectorAll('.assignment-row').length;
        }
        function addAssignmentRow() {
            const idx = nextIndex();
            const container = document.getElementById('assignments');
            const row = document.createElement('div');
            row.className = 'assignment-row grid grid-cols-1 md:grid-cols-3 gap-3 items-start';
            row.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700">Template</label>
                    <select name="checklist_assignments[${idx}][template_id]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">(None)</option>
                        @foreach ($checklistTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Component Name (optional)</label>
                    <input type="text" name="checklist_assignments[${idx}][component_name]"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="md:pt-6">
                    <button type="button"
                            class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                            onclick="removeAssignmentRow(this)">
                        Remove
                    </button>
                </div>`;
            container.appendChild(row);
        }
        function removeAssignmentRow(btn) {
            const row = btn.closest('.assignment-row');
            if (row) row.remove();
        }
    </script>
</x-app-layout>
