<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Assets from JSON') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <p class="text-sm text-gray-600 mb-4">
                        Upload a JSON file exported from this system.
                        This will create a new copy of the asset tree in your database.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 text-sm text-red-600">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('assets.import') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label for="asset_file" class="block font-medium text-sm text-gray-700">
                                Asset JSON File
                            </label>
                            <input id="asset_file"
                                   name="asset_file"
                                   type="file"
                                   class="mt-1 block w-full"
                                   accept=".json,.txt"
                                   required>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <a href="{{ route('assets.index') }}"
                               class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Import
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

