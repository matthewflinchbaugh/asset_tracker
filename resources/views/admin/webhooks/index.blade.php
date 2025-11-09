<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Webhook Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Active Webhooks</h3>
                        <a href="{{ route('webhooks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Add New Webhook
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($webhooks as $webhook)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $webhook->event_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowP text-sm text-gray-500 truncate" style="max-width: 300px;">
                                        {{ $webhook->url }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $webhook->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $webhook->is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-4">
                                            <!-- Test Button -->
                                            <form action="{{ route('webhooks.test', $webhook->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-gray-600 hover:text-gray-900 text-xs font-medium">Test</button>
                                            </form>
                                            
                                            <!-- Edit Link -->
                                            <a href="{{ route('webhooks.edit', $webhook->id) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">Edit</a>
                                            
                                            <!-- Delete Button -->
                                            <form action="{{ route('webhooks.destroy', $webhook->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this webhook?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No webhooks configured.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
