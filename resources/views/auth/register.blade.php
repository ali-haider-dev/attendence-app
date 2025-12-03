<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-6">
        <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 border border-gray-200">

            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Create Employee Account</h1>
                <p class="text-gray-500 text-sm">Register a new employee in the system</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <x-input-label for="name" :value="__('Full Name')" class="font-semibold text-gray-700" />
                    <x-text-input 
                        id="name" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" 
                        type="text" 
                        name="name" 
                        :value="old('name')" 
                        required autofocus autocomplete="name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Email Address')" class="font-semibold text-gray-700" />
                    <x-text-input 
                        id="email" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Password')" class="font-semibold text-gray-700" />
                    <x-text-input 
                        id="password" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" 
                        type="password" 
                        name="password" 
                        required autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="font-semibold text-gray-700" />
                    <x-text-input 
                        id="password_confirmation" 
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600"
                        type="password" 
                        name="password_confirmation" 
                        required autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-6">
                    <a 
                        class="text-sm text-gray-600 hover:text-indigo-600 transition" 
                        href="{{ route('login') }}"
                    >
                        Already registered?
                    </a>

                    <button 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition font-semibold"
                    >
                        Register
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
