<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit PM Checklist') }}: {{ $checklistTemplate->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded shadow-sm">
                    <strong>Whoops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form 1: Edit Template Name -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Template Details</h3>
                    <form action="{{ route('checklist-templates.update', $checklistTemplate->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="flex items-end space-x-4">
                            <div class="flex-1">
                                <label for="name" class="block font-medium text-sm text-gray-700">Template Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $checklistTemplate->name) }}" required 
                                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Save Name
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form 2: Add New Field -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Field</h3>
                    <form action="{{ route('checklist-templates.fields.store', $checklistTemplate->id) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="label" class="block font-medium text-sm text-gray-700">Field Label *</label>
                                <input type="text" name="label" id="label" placeholder="E.g., Amps L1" required 
                                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            </div>
                            <div>
                                <label for="field_type" class="block font-medium text-sm text-gray-700">Field Type *</label>
                                <select name="field_type" id="field_type" required 
                                        class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                    @foreach ($fieldTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                    Add Field
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Existing Fields -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Fields</h3>
                    <div class="space-y-2">
                        @forelse ($checklistTemplate->fields as $field)
                            <div class="flex items-center justify-between p-3 border rounded-md">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $field->label }}</span>
                                    <span class="ml-2 text-xs text-white bg-gray-500 px-2 py-0.5 rounded-full">{{ $field->field_type }}</span>
                                    <form action="{{ route('checklist-templates.fields.moveUp', [$checklistTemplate->id, $field->id]) }}" method="POST" class="inline-block ml-2">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">↑</button>
                                    </form>
                                    <form action="{{ route('checklist-templates.fields.moveDown', [$checklistTemplate->id, $field->id]) }}" method="POST" class="inline-block ml-2">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">↓</button>
                                    </form>
                                </div>
                                <form action="{{ route('checklist-templates.fields.destroy', $field->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-500">No fields have been added to this template yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
