<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Bulk Edit – Critical Infrastructure
            </h2>

            <div class="flex items-center space-x-2">
                <a href="{{ route('assets.index') }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Back to Assets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <ul class="list-disc pl-5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-sm text-gray-600 mb-4">
                        Use this screen to mark multiple assets as
                        <span class="font-semibold">Critical Infrastructure</span>.
                        If a critical child asset is out of service, its parent will
                        also be marked out of service.
                    </p>

                    <form method="POST" action="{{ route('assets.bulk_critical.update') }}">
                        @csrf

                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <button type="button"
                                        id="check-all"
                                        class="text-xs px-2 py-1 rounded border border-gray-300 bg-gray-50 hover:bg-gray-100">
                                    Check all
                                </button>
                                <button type="button"
                                        id="uncheck-all"
                                        class="text-xs px-2 py-1 rounded border border-gray-300 bg-gray-50 hover:bg-gray-100">
                                    Uncheck all
                                </button>
                            </div>

                            <p class="text-xs text-gray-500">
                                Hint: You can use your browser's search (Ctrl+F / Cmd+F) to quickly find assets.
                            </p>
                        </div>

                        <div class="overflow-x-auto max-h-[32rem] border rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 w-10">
                                            Critical
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">
                                            Asset
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">
                                            Department
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">
                                            Category
                                        </th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">
                                            Location
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($assets as $asset)
                                        <tr>
                                            {{-- Hidden field so we know this asset exists on the form --}}
                                            <input type="hidden" name="asset_ids[]" value="{{ $asset->id }}">

                                            <td class="px-3 py-2 whitespace-nowrap align-middle">
                                                <input type="checkbox"
                                                       name="critical_asset_ids[]"
                                                       value="{{ $asset->id }}"
                                                       class="critical-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                       {{ $asset->is_critical_infrastructure ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-gray-900">
                                                        {{ $asset->name }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        @if($asset->asset_tag_id)
                                                            ID: {{ $asset->asset_tag_id }}
                                                        @else
                                                            ID: —
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->department)->name ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->category)->name ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ $asset->location ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                                No assets found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <a href="{{ route('assets.index') }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </form>

                    {{-- Tiny inline script for check all / uncheck all --}}
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const checkAllBtn = document.getElementById('check-all');
                            const uncheckAllBtn = document.getElementById('uncheck-all');
                            const boxes = document.querySelectorAll('.critical-checkbox');

                            if (checkAllBtn) {
                                checkAllBtn.addEventListener('click', function () {
                                    boxes.forEach(cb => cb.checked = true);
                                });
                            }

                            if (uncheckAllBtn) {
                                uncheckAllBtn.addEventListener('click', function () {
                                    boxes.forEach(cb => cb.checked = false);
                                });
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

