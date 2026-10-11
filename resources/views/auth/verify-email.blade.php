<x-guest-layout>
    <h2 class="text-2xl font-extrabold text-base-content mb-1">Verificar Correo</h2>
    <p class="text-xs text-base-content/60 mb-6">
        ¡Gracias por registrarte! Antes de continuar, verificá tu correo haciendo clic en el enlace que te acabamos de enviar. Si no lo recibiste, podés pedir otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-semibold text-xs text-success">
            Se envió un nuevo enlace de verificación al correo electrónico con el que te registraste.
        </div>
    @endif

    <div class="space-y-3 mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full font-bold uppercase text-xs">
                Reenviar correo de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-xs text-base-content/70 hover:text-error hover:underline">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>