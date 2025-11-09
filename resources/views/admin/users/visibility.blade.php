<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Visibility') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Tag Permissions for: <span class="font-bold">{{ $user->name }} ({{ ucfirst($user->role) }})</span>
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Check the boxes below to grant this user visibility to assets with these tags.
                        If no boxes are checked, the user will see NO assets.
                    </p>

                    <form action="{{ route('users.visibility.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <!-- FIX: Loop over $tags, not $categories -->
                            @forelse ($tags as $tag)
                                <div class="flex items-center">
                                    <input type="checkbox" name="category_ids[]" id="category_{{ $tag->id }}" value="{{ $tag->id }}"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           @checked(in_array($tag->id, $assignedCategories))>
                                    <label for="category_{{ $tag->id }}" class="ml-3 text-sm font-medium text-gray-700">
                                        {{ $tag->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-red-500">No tags found. Please create tags first.</p>
                            @endforelse
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Update Visibility
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
