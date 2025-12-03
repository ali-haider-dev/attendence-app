<x-guest-layout>

    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-8 border border-gray-200">

        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Login</h1>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Employee Management System</h1>
            <p class="text-gray-500 text-sm dark:text-white">Log in to continue</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <x-input-label for="email" :value="__('Email')" class="font-semibold text-gray-700 dark:text-white" />
                <x-text-input id="email"
                    class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600"
                    type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <x-input-label for="password" :value="__('Password')"
                    class="font-semibold text-gray-700 dark:text-white" />
                <x-text-input id="password"
                    class="block mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring-indigo-600"
                    type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center mb-4">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" name="remember">
                <label for="remember_me" class="ml-2 text-sm text-gray-700 dark:text-white">Remember me</label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-6">
                @if (Route::has('password.request'))
                    <a class="text-sm text-gray-600 dark:text-white hover:text-indigo-600 transition"
                        href="{{ route('password.request') }}">
                        Forgot Password?
                    </a>
                @endif

                <button
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition font-semibold">
                    Log in
                </button>
            </div>
        </form>
    </div>

</x-guest-layout>