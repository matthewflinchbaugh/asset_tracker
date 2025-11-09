<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New System User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        User Details
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

                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        
                        <!-- Name -->
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                            <input type="password" name="password" id="password" required
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                        </div>

                        <!-- Role Dropdown -->
                        <div class="mt-4">
                            <label for="role" class="block font-medium text-sm text-gray-700">Assign Role</label>
                            <select name="role" id="role" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technician</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>


                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Create User
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
