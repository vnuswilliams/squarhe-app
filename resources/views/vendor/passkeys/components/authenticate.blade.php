<div>
    @include('passkeys::components.partials.authenticateScript')

    <form id="passkey-login-form" method="POST" action="{{ route('passkeys.login') }}">
        @csrf
    </form>

    @if($message = session()->get('authenticatePasskey::message'))
        <div class="bg-red-100 text-red-700 p-4 border border-red-400 rounded">
            {{ $message }}
        </div>
    @endif

    <div onclick="authenticateWithPasskey()">
        @if ($slot->isEmpty())
            <flux:button icon="finger-print" class="w-full" >
                {{ __('passkeys::passkeys.authenticate_using_passkey') }}
            </flux:button>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
