<div
    x-data="{
        theme: localStorage.getItem('theme') || 'light',
        init() {
            window.setupDualLogos();
            this.applyTheme(this.theme);
        },
        toggle() {
            this.theme = (this.theme === 'light' ? 'dark' : 'light');
            localStorage.setItem('theme', this.theme);
            this.applyTheme(this.theme);
        },
        applyTheme(t) {
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            window.dispatchEvent(new CustomEvent('theme-changed', { detail: t }));

            if (window.Alpine && window.Alpine.store('theme')) {
                window.Alpine.store('theme', t);
            }
        }
    }"
    x-init="init()"
    class="flex items-center"
>
    <button
        @click="toggle()"
        type="button"
        class="flex items-center justify-center p-2 mr-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors"
        title="Cambiar tema (Claro / Oscuro)"
    >
        <!-- Icono Luna (se ve en Modo Claro) -->
        <svg x-show="theme === 'light'" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>

        <!-- Icono Sol (se ve en Modo Oscuro) -->
        <svg x-show="theme === 'dark'" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>
</div>

<script>
    window.setupDualLogos = function () {
        document.querySelectorAll('.fi-sidebar-header, .fi-topbar').forEach((container) => {
            if (container.querySelector('.sh-logo-dark')) return;

            const logos = container.querySelectorAll('img.fi-logo');
            if (logos.length === 0) return;

            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'display: flex; flex-direction: row; align-items: center;';
            wrapper.innerHTML = `
                <div class="sh-logo-light" style="display: flex; flex-direction: row; align-items: center; gap: 10px; flex-wrap: nowrap;">
                    <div style="height: 44px; width: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="{{ asset('img/LogoTiendaNegro.png') }}" alt="Sound Heritage Icono" style="height: 44px; width: 44px; object-fit: contain; display: block;" />
                    </div>
                    <img src="{{ asset('img/LetrasTiendaNegro.png') }}" alt="Sound Heritage" style="height: 24px; width: auto; object-fit: contain; display: block; flex-shrink: 0;" />
                </div>
                <div class="sh-logo-dark" style="display: none; flex-direction: row; align-items: center; gap: 10px; flex-wrap: nowrap;">
                    <div style="height: 44px; width: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="{{ asset('img/LogoTiendaBlanco.png') }}" alt="Sound Heritage Icono" style="height: 92px; width: auto; max-width: none; object-fit: contain; transform: translate(1px, 11.5px); display: block; pointer-events: none;" />
                    </div>
                    <img src="{{ asset('img/LetrasTiendaBlanco.png') }}" alt="Sound Heritage" style="height: 24px; width: auto; object-fit: contain; display: block; flex-shrink: 0;" />
                </div>
            `;

            logos[0].parentNode.insertBefore(wrapper, logos[0]);
            logos.forEach(img => img.remove());
        });
    };

    document.addEventListener('DOMContentLoaded', () => window.setupDualLogos());

    document.addEventListener('livewire:navigated', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        window.setupDualLogos();
        if (window.Alpine && window.Alpine.store('theme')) {
            window.Alpine.store('theme', savedTheme);
        }
    });
</script>