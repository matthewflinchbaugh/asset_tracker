<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asset Details') }}: {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('secure_link'))
                <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-sm">
                    <h4 class="font-bold">Secure Contractor Link Generated</h4>
                    <p class="text-sm">Copy this link and send it to your contractor. It is valid for 7 days.</p>
                    <input type="text" readonly value="{{ session('secure_link') }}" 
                           class="mt-2 block w-full rounded-md shadow-sm border-gray-300 bg-gray-50 text-sm" 
                           onclick="this.select(); document.execCommand('copy');" 
                           title="Click to copy link">
                </div>
            @endif

            <!-- Main Action Buttons -->
            <div class="flex flex-wrap justify-end gap-4 mb-4">
                <a href="{{ route('assets.logs.create', $asset->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                    Add Maintenance Log
                </a>
                
                <a href="{{ route('assets.location.edit', $asset->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                    Change Location
                </a>
                
                @if (auth()->user()->role == 'admin' || auth()->user()->role == 'manager')
                <form action="{{ route('assets.logs.generate_link', $asset->id) }}" method="POST" class="flex-shrink-0" onsubmit="return confirm('This will create a new public link for this asset. Continue?');">
                    @csrf
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <input type="text" name="contractor_company" placeholder="Contractor Company *" required 
                               class="rounded-md shadow-sm border-gray-300 text-sm w-full sm:w-36" value="{{ old('contractor_company') }}">
                        <input type="text" name="contractor_rep" placeholder="Rep Name *" required 
                               class="rounded-md shadow-sm border-gray-300 text-sm w-full sm:w-36" value="{{ old('contractor_rep') }}">
                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                            Generate Contractor Link
                        </button>
                    </div>
                </form>
                @endif
                
                @if (auth()->user()->role == 'admin')
                <a href="{{ route('assets.edit', $asset->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Edit Asset
                </a>
                @endif
            </div>
            
            <!-- Main Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Column 1: Core Details -->
                        <div class="md:col-span-2 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                                {{ $asset->name }}
                                <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium {{ $asset->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $asset->status }}
                                </span>
                            </h3>
                            
                            @if ($asset->parent)
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold">Part Of Line/System:</span> 
                                    <a href="{{ route('assets.show', $asset->parent->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $asset->parent->name }} ({{ $asset->parent->asset_tag_id }})
                                    </a>
                                </div>
                            @endif

                            <div>
                                <span class="font-semibold">Asset ID:</span> {{ $asset->asset_tag_id }}
                            </div>
                            <div>
                                <span class="font-semibold">Primary Tag Group:</span> {{ $asset->category->name ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Tags:</span>
                                @forelse ($asset->tags as $tag)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                        {{ $tag->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-500">None</span>
                                @endforelse
                            </div>
                            <div>
                                <span class="font-semibold">Department:</span> {{ $asset->department->name }}
                            </div>
                            <div>
                                <span class="font-semibold">Location:</span> {{ $asset->location }}
                            </div>
                            <h4 class="text-md font-medium text-gray-900 pt-4">Manufacturer Details</h4>
                            <div>
                                <span class="font-semibold">Manufacturer:</span> {{ $asset->manufacturer ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Model:</span> {{ $asset->model_number ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Serial:</span> {{ $asset->serial_number ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Column 2: QR Code & Financials -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                                Asset ID
                            </h3>
                            <div>
                                <img src="{!! 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode(route('assets.show', $asset->id)) !!}" 
                                     alt="QR Code for {{ $asset->asset_tag_id }}">
                            </div>
                            <h4 class="text-md font-medium text-gray-900 pt-4">Financials</h4>
                            <div>
                                <span class="font-semibold">Cost:</span> ${{ number_format($asset->purchase_cost, 2) }}
                            </div>
                            <div>
                                <span class="font-semibold">Purchase Date:</span> {{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('M d, Y') : 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Warranty Expires:</span> {{ $asset->warranty_expiration_date ? \Carbon\Carbon::parse($asset->warranty_expiration_date)->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- Children Assets (Component List) -->
            @if ($asset->children->count() > 0)
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Component Assets ({{ $asset->children->count() }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($asset->children as $child)
                            <div class="border rounded-lg p-3 shadow-sm">
                                <a href="{{ route('assets.show', $child->id) }}" class="font-semibold text-blue-600 hover:text-blue-900">
                                    {{ $child->name }} ({{ $child->asset_tag_id }})
                                </a>
                                <p class="text-sm text-gray-600">{{ $child->category->name ?? 'N/A' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- PM Schedule -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Maintenance Schedule
                    </h3>
                    @if ($asset->checklistTemplates)
                        @if ($asset->checklistTemplates->count())
                            <p class="mb-2"><span class="font-semibold">Assigned PM Checklists:</span>
                                @foreach ($asset->checklistTemplates as $assign)
                                    {{ $assign->name }}@if($assign->pivot->component_name) ({{ $assign->pivot->component_name }})@endif @if (! $loop->last), @endif
                                @endforeach
                            </p>
                        @endif
                    @endif
                    <p>
                        <span class="font-semibold">Interval:</span> 
                        {{ $asset->pm_interval_value ? 'Every ' . $asset->pm_interval_value . ' ' . $asset->pm_interval_unit : 'Not set' }}
                    </p>
                    <p>
                        <span class="font-semibold">Next Due Date:</span> 
                        {{ $asset->next_pm_due_date ? \Carbon\Carbon::parse($asset->next_pm_due_date)->format('M d, Y') : 'Not set' }}
                    </p>
                    <p class="mt-2">
                        <span class="font-semibold">Standard Procedure Notes:</span><br>
                        <span class="text-sm text-gray-600 whitespace-pre-wrap">{{ $asset->pm_procedure_notes ?? 'No procedure notes entered.' }}</span>
                    </p>
                </div>
            </div>

            <!-- Log History -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Log History
                    </h3>
                    
                    <div class="space-y-4">
                        @forelse ($asset->maintenanceLogs->where('is_draft', false)->sortByDesc('service_date') as $log)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-md">{{ $log->event_type_display }}</span>
                                    <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($log->service_date)->format('M d, Y') }}</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Logged by: 
                                    @if($log->user_id)
                                        {{ $log->user->name }}
                                    @elseif($log->contractor_company)
                                        {{ $log->contractor_rep }} ({{ $log->contractor_company }})
                                    @else
                                        System
                                    @endif
                                </p>
                                
                                <!-- Display Checklist Data OR Standard Description -->
                                @if ($log->checklistData->isNotEmpty())
                                    <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-1">
                                        @foreach ($log->checklistData as $data)
                                            <div class="text-sm">
                                                <span class="text-gray-500">{{ $data->field->label }}:</span>
                                                <span class="font-medium text-gray-800">
                                                    @if ($data->field->field_type == 'checkbox')
                                                        {{ $data->value == 'true' ? 'Yes' : 'No' }}
                                                    @else
                                                        {{ $data->value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($log->description_of_work != optional($log->asset->checklistTemplates->first())->name . ' completed.')
                                        <p class="mt-2 text-gray-700 text-sm">
                                            <span class="font-semibold">Notes:</span> {{ $log->description_of_work }}
                                        </p>
                                    @endif
                                @else
                                    <p class="mt-2 text-gray-700">{{ $log->description_of_work }}</p>
                                @endif
                                
                                @if ($log->attachments->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <span class="font-semibold text-xs uppercase text-gray-600">Attachments:</span>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach ($log->attachments as $attachment)
                                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900 underline" title="{{ $attachment->original_file_name }}">
                                                {{ Str::limit($attachment->original_file_name, 20) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <div class="text-sm mt-2 pt-2 border-t border-gray-100">
                                    <span class="font-semibold">Parts Cost:</span> ${{ number_format($log->parts_cost, 2) }} | 
                                    <span class="font-semibold">Labor Hours:</span> {{ $log->labor_hours }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No maintenance logs found for this asset.</p>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
