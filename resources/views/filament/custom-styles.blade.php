<link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaNegro.png') }}" media="(prefers-color-scheme: light)">
<link rel="icon" type="image/png" href="{{ asset('img/LogoTiendaBlanco.png') }}" media="(prefers-color-scheme: dark)">
<style>
    /* ==========================================================================

    /* ==========================================================================
       Sound Heritage - Corporate Visual Identity (Light & Dark Mode)
       ========================================================================== */

    /* TIPOGRAFÍA GLOBAL */
    body, .fi-body, .fi-layout {
        font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif !important;
    }

    /* --------------------------------------------------------------------------
       1. MODO CLARO (Default cuando no es .dark)
       -------------------------------------------------------------------------- */
    html:not(.dark) body, html:not(.dark) .fi-body, html:not(.dark) .fi-layout {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }
    html:not(.dark) .fi-topbar, html:not(.dark) .fi-topbar nav {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    html:not(.dark) .fi-sidebar, html:not(.dark) .fi-sidebar-header {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
    }
    html:not(.dark) .fi-header-heading {
        color: #0b1031 !important;
    }
    html:not(.dark) .fi-section, html:not(.dark) .fi-wi-widget, html:not(.dark) .fi-ta-ctn {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-top: 4px solid #b48d56 !important;
    }
    html:not(.dark) .fi-ta-record {
        background-color: #ffffff !important;
    }
    html:not(.dark) .fi-ta-record:hover {
        background-color: #f8fafc !important;
    }
    html:not(.dark) .fi-ta-cell, html:not(.dark) .fi-ta-cell span {
        color: #1e293b !important;
    }
    html:not(.dark) .fi-sidebar-item-button {
        color: #334155 !important;
    }

    /* --------------------------------------------------------------------------
       2. MODO OSCURO (Azul Marino Noche #070a1e y Slate)
       -------------------------------------------------------------------------- */
    html.dark body, html.dark .fi-body, html.dark .fi-layout {
        background-color: #070a1e !important;
        color: #f1f5f9 !important;
    }
    html.dark .fi-topbar, html.dark .fi-topbar nav {
        background-color: #0b1031 !important;
        border-bottom: 1px solid rgba(180, 141, 86, 0.25) !important;
    }
    html.dark .fi-sidebar, html.dark .fi-sidebar-header {
        background-color: #0b1031 !important;
        border-right: 1px solid rgba(180, 141, 86, 0.25) !important;
    }
    html.dark .fi-header-heading {
        color: #f8fafc !important;
    }
    html.dark .fi-section, html.dark .fi-wi-widget, html.dark .fi-ta-ctn {
        background-color: #0f172a !important;
        border: 1px solid rgba(180, 141, 86, 0.2) !important;
        border-top: 4px solid #b48d56 !important;
    }
    html.dark .fi-ta-record {
        background-color: #0f172a !important;
    }
    html.dark .fi-ta-record:hover {
        background-color: #1e293b !important;
    }
    html.dark .fi-ta-cell, html.dark .fi-ta-cell span {
        color: #e2e8f0 !important;
    }
    html.dark .fi-sidebar-item-button {
        color: #cbd5e1 !important;
    }

    /* --------------------------------------------------------------------------
       3. ELEMENTOS COMPARTIDOS (Dorado y Encabezados Navy)
       -------------------------------------------------------------------------- */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #0b1031 !important;
        color: #ffffff !important;
        border-left: 4px solid #b48d56 !important;
        font-weight: 700 !important;
    }
    html.dark .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #1e293b !important;
        border-left: 4px solid #b48d56 !important;
    }
    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #b48d56 !important;
    }

    /* Encabezado de Tablas: Navy #0b1031 con borde dorado siempre */
    .fi-ta-table thead, .fi-ta-table thead tr, .fi-ta-table thead th {
        background-color: #0b1031 !important;
        border-bottom: 2px solid #b48d56 !important;
    }
    .fi-ta-header-cell span {
        color: #ffffff !important;
        text-transform: uppercase !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
    }
    .fi-ta-header-cell svg {
        color: #b48d56 !important;
    }

    /* Botón Principal Dorado */
    .fi-btn-primary {
        background-color: #b48d56 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .fi-btn-primary:hover {
        background-color: #9a7645 !important;
    }

    /* Logo y Switch Instantáneo 0ms por CSS */
    .fi-logo, .fi-logo img, img.fi-logo {
        max-height: 3.2rem !important;
        width: auto !important;
        object-fit: contain !important;
    }
    html:not(.dark) .sh-logo-light { display: flex !important; }
    html:not(.dark) .sh-logo-dark  { display: none !important; }
    html.dark .sh-logo-light       { display: none !important; }
    html.dark .sh-logo-dark        { display: flex !important; }
        /* Oculta el selector de tema nativo dentro del menú desplegable del empleado */
    .fi-dropdown-list:has(.fi-theme-switcher), .fi-theme-switcher { display: none !important; }
    @media print {
        .fi-sidebar, .fi-topbar, .fi-btn, .fi-header-actions {
            display: none !important;
        }
        .fi-main {
            padding: 0 !important;
        }
    }
</style>