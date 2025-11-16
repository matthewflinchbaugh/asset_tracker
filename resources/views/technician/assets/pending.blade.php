<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Pending Assets
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-4 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Search --}}
            <div class="mb-4">
                <form method="GET" action="{{ route('technician.pending-assets.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by name, asset ID, department..."
                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm"
                    >
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
                    >
                        Search
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($assets->isEmpty())
                        <p class="text-sm text-gray-600">
                            You do not currently have any pending assets awaiting approval.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Asset ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Name</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Department</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Category</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Created</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($assets as $asset)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-800">
                                                {{ $asset->asset_tag_id ?? 'Pending ID' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-800">
                                                {{ $asset->name }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->department)->name ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->category)->name ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-500 text-xs">
                                                {{ $asset->created_at?->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <a
                                                        href="{{ route('assets.show', $asset->id) }}"
                                                        class="text-xs text-gray-700 hover:text-gray-900 underline"
                                                    >
                                                        View
                                                    </a>
                                                    <a
                                                        href="{{ route('assets.edit', $asset->id) }}"
                                                        class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-500"
                                                    >
                                                        Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $assets->withQueryString()->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

