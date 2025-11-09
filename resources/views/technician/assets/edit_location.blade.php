<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Update Asset Location') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ $asset->name }} ({{ $asset->asset_tag_id }})
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Current Location: <span class="font-medium">{{ $asset->location ?? 'N/A' }}</span>
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

                    <form action="{{ route('assets.location.update', $asset->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Location -->
                        <div>
                            <label for="location" class="block font-medium text-sm text-gray-700">New Location *</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $asset->location) }}" required 
                                   class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('assets.show', $asset->id) }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Update Location
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
