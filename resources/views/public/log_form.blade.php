<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            Contractor Submission
        </h2>
        <p class="mb-2">Submitting log for: <span class="font-bold">{{ $asset->name }} ({{ $asset->asset_tag_id }})</span></p>
        <p class="mb-4">
            Company: <span class="font-medium">{{ $log->contractor_company }}</span> | 
            Rep: <span class="font-medium">{{ $log->contractor_rep }}</span>
        </p>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600">Whoops! Something went wrong.</div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('public.log.store', $token) }}" enctype="multipart/form-data">
        @csrf
        
        <!-- Contractor Name Fields (Read-only hidden fields, data already stored in draft) -->
        <input type="hidden" name="contractor_company" value="{{ $log->contractor_company }}">
        <input type="hidden" name="contractor_rep" value="{{ $log->contractor_rep }}">

        <!-- Event Type -->
        <div class="mt-4">
            <label for="event_type" class="block font-medium text-sm text-gray-700">Event Type *</label>
            <select name="event_type" id="event_type" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                <option value="">Select an event type...</option>
                <option value="scheduled_maintenance">Scheduled Maintenance</option>
                <option value="unscheduled_repair" selected>Unscheduled Repair</option>
                <option value="inspection">Inspection</option>
            </select>
        </div>

        <!-- Service Date -->
        <div class="mt-4">
            <label for="service_date" class="block font-medium text-sm text-gray-700">Service Date *</label>
            <input type="datetime-local" name="service_date" id="service_date" value="{{ now()->toDateTimeLocalString() }}" required 
                   class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
        </div>

        <!-- Description -->
        <div class="mt-4">
            <label for="description_of_work" class="block font-medium text-sm text-gray-700">Description of Work Performed *</label>
            <textarea name="description_of_work" id="description_of_work" rows="5" required
                      class="block mt-1 w-full rounded-md shadow-sm border-gray-300">{{ old('description_of_work') }}</textarea>
        </div>

        <!-- Costs -->
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
                <label for="parts_cost" class="block font-medium text-sm text-gray-700">Parts Cost ($)</label>
                <input type="number" name="parts_cost" id="parts_cost" value="0.00" step="0.01" min="0"
                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>
            <div>
                <label for="labor_hours" class="block font-medium text-sm text-gray-700">Labor Hours</label>
                <input type="number" name="labor_hours" id="labor_hours" value="0.00" step="0.25" min="0"
                       class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>
        </div>
        
        <!-- File Attachments -->
        <div class="mt-4">
            <label for="attachments" class="block font-medium text-sm text-gray-700">Attachments (Max 5 files, 5MB each)</label>
            <input type="file" name="attachments[]" id="attachments" multiple 
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="ms-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Submit Log
            </button>
        </div>
    </form>
</x-guest-layout>
