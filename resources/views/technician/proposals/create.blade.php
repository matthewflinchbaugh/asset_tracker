<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Asset Proposal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Submit a Proposal for New Equipment
                    </h3>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('proposals.store') }}" method="POST">
                        @csrf
                        
                        <!-- Asset Name -->
                        <div>
                            <label for="asset_name" class="block font-medium text-sm text-gray-700">Proposed Asset Name *</label>
                            <input type="text" name="asset_name" id="asset_name" value="{{ old('asset_name') }}" required 
                                   class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Reason -->
                        <div class="mt-4">
                            <label for="reason" class="block font-medium text-sm text-gray-700">Reason / Justification *</label>
                            <textarea name="reason" id="reason" rows="5" required
                                      class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('reason') }}</textarea>
                        </div>
                        
                        <!-- Estimated Cost -->
                        <div class="mt-4">
                            <label for="estimated_cost" class="block font-medium text-sm text-gray-700">Estimated Cost (e.g., "$1500" or "Quote attached")</label>
                            <input type="text" name="estimated_cost" id="estimated_cost" value="{{ old('estimated_cost') }}"
                                   class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>
                        <!-- We can add file uploads for quotes later -->

                        <!-- Actions -->
                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Submit Proposal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
