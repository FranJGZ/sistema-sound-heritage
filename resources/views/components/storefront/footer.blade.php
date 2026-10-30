<!-- ========================================================================= -->
<!-- PIE DE PÁGINA CANÓNICO SOUND HERITAGE (Visible en todas las vistas)       -->
<!-- ========================================================================= -->
<footer class="bg-[#181715] text-white border-t border-[#7E2B2D]/20 mt-16 pt-12 pb-8 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-8">
        
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-white/10">
            <!-- Isologotipo -->
            <a href="/" class="flex items-center gap-2.5 hover:opacity-90 transition-opacity">
                <img 
                    src="{{ asset('img/LogoTiendaBlanco.png') }}" 
                    alt="Sound Heritage Icono" 
                    class="h-8 w-auto object-contain" 
                />
                <img 
                    src="{{ asset('img/LetrasTiendaBlanco.png') }}" 
                    alt="Sound Heritage" 
                    class="h-5 w-auto object-contain" 
                />
            </a>

            <!-- Enlaces Rápidos de Navegación -->
            <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-zinc-300">
                <a href="{{ route('productos') }}" class="hover:text-[#D97724] transition-colors">Productos</a>
                <a href="{{ route('ofertas') }}" class="hover:text-[#D97724] transition-colors">Ofertas</a>
                <a href="{{ route('eventos') }}" class="hover:text-[#D97724] transition-colors">Eventos</a>
                <a href="{{ route('reservar-salon') }}" class="hover:text-[#D97724] transition-colors">Reservar Salón</a>
                <a href="{{ route('mi-cuenta') }}" class="hover:text-[#D97724] transition-colors">Mi Cuenta</a>
            </div>

            <!-- Redes Sociales en Círculos -->
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-xs font-bold hover:bg-[#D97724] hover:border-[#D97724] hover:text-white cursor-pointer transition-colors" title="Instagram">IG</span>
                <span class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-xs font-bold hover:bg-[#D97724] hover:border-[#D97724] hover:text-white cursor-pointer transition-colors" title="YouTube">YT</span>
                <span class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-xs font-bold hover:bg-[#D97724] hover:border-[#D97724] hover:text-white cursor-pointer transition-colors" title="Facebook">FB</span>
                <span class="w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-xs font-bold hover:bg-[#D97724] hover:border-[#D97724] hover:text-white cursor-pointer transition-colors" title="TikTok">TK</span>
            </div>
        </div>

        <!-- Copyright Legítimo y Showroom -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-400">
            <p>© {{ date('Y') }} Sound Heritage S.R.L. - Espacio Cultural &amp; Tienda Oficial de Instrumentos Musicales.</p>
            <p>Showroom &amp; Auditorio: Calle Colón 1234, Posadas, Misiones · Lunes a Sábados de 10:00 a 20:00 hs.</p>
        </div>
    </div>
</footer>

