<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Draft Maintenance Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Incomplete Logs
                    </h3>

                    <div class="space-y-4">
                        @forelse ($drafts as $draft)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <a href="{{ route('assets.show', $draft->asset_id) }}" class="font-semibold text-md text-blue-600 hover:text-blue-900">
                                            {{ $draft->asset->name }}
                                        </a>
                                        <span class="text-sm text-gray-600">({{ $draft->asset->asset_tag_id }})</span>
                                    </div>
                                    <span class="text-sm text-gray-500">Last saved: {{ $draft->updated_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-2 text-gray-700 italic">
                                    {{ Str::limit($draft->description_of_work, 150) }}
                                </p>
                                <div class="flex items-center space-x-4 mt-3 pt-3 border-t">
                                    <a href="{{ route('logs.draft.edit', $draft->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                        Resume Editing
                                    </a>
                                    <form action="{{ route('logs.draft.destroy', $draft->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this draft?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium">Delete Draft</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">You have no saved drafts.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
