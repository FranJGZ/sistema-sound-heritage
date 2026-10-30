<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Verificar Correo</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">
            ¡Gracias por registrarte! Antes de continuar, verificá tu casilla haciendo clic en el enlace que te acabamos de enviar por correo. Si no lo recibiste, podés solicitar otro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 font-bold text-xs text-emerald-700 dark:text-emerald-400 text-center">
            Se envió un nuevo enlace de verificación al correo con el que te diste de alta.
        </div>
    @endif

    <div class="space-y-3 mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-xl bg-[#D97724] hover:bg-[#c06517] text-white font-black uppercase tracking-wide text-xs shadow-md shadow-[#D97724]/20 transition-all duration-150 cursor-pointer"
            >
                Reenviar correo de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center pt-2">
            @csrf
            <button type="submit" class="text-xs text-zinc-500 dark:text-zinc-400 hover:text-rose-500 hover:underline font-bold transition-colors">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
