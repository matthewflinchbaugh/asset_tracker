<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New PM Checklist') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('checklist-templates.store') }}" method="POST">
                        @csrf
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Template Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                   class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                            <p class="text-xs text-gray-500 mt-1">E.g., "Monthly Motor PM", "HVAC Inspection", etc.</p>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('checklist-templates.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Create and Add Fields
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
