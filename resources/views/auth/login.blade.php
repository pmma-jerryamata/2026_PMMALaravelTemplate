@extends('layouts.admin')
@section('content')
<section class="pt-4">
    <div class="p-4 p-lg-1 bg-light rounded-3 text-center" style="display: grid; place-items: center">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div >
            <br>
            <table>
                <tr>
                    <td width="30%" align="right"><x-input-label for="email" :value="__('Email : ')" /></td>
                    <td width="70%"><x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" /></td>
                </tr>
                <tr>
                    <td colspan="2" width="100%"></td>
                </tr>
                <tr>
                    <td width="30%" align="right"><x-input-label for="password" :value="__('Password : ')" /></td>
                    <td width="70%"><x-text-input id="password" class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required autocomplete="current-password" /></td>
                </tr>
                <tr>
                    <td colspan="2" width="100%">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </td>
                </tr>
            

        <!--Remember Me
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            -->
                <tr>
                    <td colspan="2" width="100%" align='center'>
                        <x-primary-button class="btn btn-primary btn-lg">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </td>
                </tr>
                
        </table>
        <br>
        </div>
    </form>
    </div>
</section>
@endsection