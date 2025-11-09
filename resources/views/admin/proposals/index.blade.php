<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Asset Proposals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Submitted Proposals
                    </h3>

                    <div class="space-y-6">
                        @forelse ($proposals as $proposal)
                            <div class="border rounded-lg p-4 {{ $proposal->status == 'pending' ? 'bg-yellow-50' : 'bg-gray-50' }}">
                                <div class="flex justify-between items-center">
                                    <h4 class="font-semibold text-md">{{ $proposal->asset_name }}</h4>
                                    <span class="text-sm font-medium px-2 py-0.5 rounded
                                        @switch($proposal->status)
                                            @case('pending') bg-yellow-200 text-yellow-800 @break
                                            @case('approved') bg-green-200 text-green-800 @break
                                            @case('denied') bg-red-200 text-red-800 @break
                                        @endswitch
                                    ">
                                        {{ ucfirst($proposal->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    Proposed by: {{ $proposal->user->name ?? 'Unknown' }} on {{ $proposal->created_at->format('M d, Y') }}
                                </p>
                                <p class="mt-2 text-gray-700"><strong>Reason:</strong> {{ $proposal->reason }}</p>
                                <p class="mt-1 text-gray-700"><strong>Est. Cost:</strong> {{ $proposal->estimated_cost ?? 'N/A' }}</p>
                                
                                @if ($proposal->status != 'pending')
                                    <div class="mt-3 pt-3 border-t">
                                        <p class="text-sm font-semibold">Reviewed by: {{ $proposal->reviewer->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-600">Notes: {{ $proposal->admin_notes ?? 'N/A' }}</p>
                                    </div>
                                @endif

                                @if ($proposal->status == 'pending')
                                    <form action="{{ route('proposals.update', $proposal->id) }}" method="POST" class="mt-4 pt-4 border-t">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex items-center space-x-4">
                                            <select name="status" class="rounded-md shadow-sm border-gray-300">
                                                <option value="approved">Approve</option>
                                                <option value="denied">Deny</option>
                                            </select>
                                            <input type="text" name="admin_notes" placeholder="Optional notes..." class="flex-1 rounded-md shadow-sm border-gray-300">
                                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">No new proposals found.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
