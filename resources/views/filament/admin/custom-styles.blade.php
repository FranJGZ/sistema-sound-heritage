<style>
    /* ==========================================================================
       Sound Heritage - High-Contrast Corporate Visual Identity for Filament v3
       ========================================================================== */

    /* 1. Fondo general del panel y tipografía */
    body, .fi-body, .fi-layout {
        background-color: #f8fafc !important; /* Slate 50 */
        font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif !important;
        color: #0f172a !important;
    }

    /* 2. Barra Superior (Topbar) */
    .fi-topbar {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    }

    /* 3. Encabezados de página y títulos */
    .fi-header-heading,
    h1.fi-header-heading {
        color: #0b1031 !important; /* Azul Marino Corporativo */
        font-weight: 800 !important;
        font-size: 1.75rem !important;
        letter-spacing: -0.02em !important;
    }

    .fi-breadcrumbs,
    .fi-breadcrumbs a,
    .fi-breadcrumbs span {
        color: #64748b !important; /* Slate 500 */
        font-weight: 500 !important;
        font-size: 0.825rem !important;
    }

    /* 4. Barra lateral (Sidebar) */
    .fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: 1px 0 3px 0 rgba(0, 0, 0, 0.02) !important;
    }

    /* Elementos inactivos del sidebar (bien visibles y contrastados) */
    .fi-sidebar-item-button {
        color: #334155 !important;
        font-weight: 600 !important;
        border-radius: 0.5rem !important;
        transition: all 0.15s ease-in-out !important;
    }

    .fi-sidebar-item-button span,
    .fi-sidebar-item-button .fi-sidebar-item-label {
        color: #334155 !important;
        font-weight: 600 !important;
    }

    .fi-sidebar-item-icon {
        color: #64748b !important;
        transition: color 0.15s ease !important;
    }

    .fi-sidebar-item-button:hover:not(.fi-sidebar-item-active .fi-sidebar-item-button) {
        background-color: #f1f5f9 !important;
        color: #0b1031 !important;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        color: #b48d56 !important;
    }

    /* Elemento activo del sidebar: Fondo Navy con borde izquierdo dorado */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background-color: #0b1031 !important;
        color: #ffffff !important;
        border-left: 4px solid #b48d56 !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 4px rgba(11, 16, 49, 0.15) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button span,
    .fi-sidebar-item-active .fi-sidebar-item-button .fi-sidebar-item-label {
        color: #ffffff !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-icon {
        color: #b48d56 !important;
    }

    /* 5. Tarjetas, Secciones y Contenedores: Borde superior dorado #b48d56 */
    .fi-section,
    .fi-wi-widget {
        border-top: 4px solid #b48d56 !important;
        border-radius: 0.75rem !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04) !important;
        border-left: 1px solid #e2e8f0 !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        overflow: visible !important;
    }

    .fi-section-content-ctn,
    .fi-section-content {
        overflow: visible !important;
    }

    .fi-ta-ctn {
        border-top: 4px solid #b48d56 !important;
        border-radius: 0.75rem !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04) !important;
        border-left: 1px solid #e2e8f0 !important;
        border-right: 1px solid #e2e8f0 !important;
        border-bottom: 1px solid #e2e8f0 !important;
        overflow: hidden;
    }

    /* Z-Index elevado para que los menús desplegables queden siempre al frente */
    .fi-dropdown-panel,
    .fi-select-input-options-container,
    .choices__list--dropdown,
    [role="listbox"],
    .fi-popover-content {
        z-index: 9999 !important;
    }

    /* 6. Encabezado de Tablas (thead): Navy #0b1031 con texto blanco */
    .fi-ta-table thead,
    .fi-ta-table thead tr,
    .fi-ta-table thead th {
        background-color: #0b1031 !important;
        border-bottom: 2px solid #b48d56 !important;
    }

    .fi-ta-header-cell,
    .fi-ta-header-cell button,
    .fi-ta-header-cell span {
        color: #ffffff !important;
        text-transform: uppercase !important;
        font-size: 0.72rem !important;
        letter-spacing: 0.06em !important;
        font-weight: 700 !important;
    }

    .fi-ta-header-cell svg,
    .fi-ta-header-cell button svg {
        color: #b48d56 !important;
    }

    /* 7. Filas y Celdas de la Tabla (TEXTO SIEMPRE OSCURO Y LEGIBLE) */
    .fi-ta-record {
        background-color: #ffffff !important;
        transition: background-color 0.15s ease-in-out !important;
    }

    .fi-ta-record:hover {
        background-color: #f8fafc !important; /* Hover suave */
    }

    /* Asegurar que el texto dentro de las celdas sea visible en fondo blanco */
    .fi-ta-cell,
    .fi-ta-cell span,
    .fi-ta-text-item,
    .fi-ta-text-item span,
    .fi-ta-text-item-label {
        color: #1e293b !important; /* Slate 800 */
        font-size: 0.875rem !important;
    }

    /* Checkboxes de tabla con borde visible */
    .fi-ta-record input[type="checkbox"],
    .fi-ta-header-cell input[type="checkbox"] {
        border-color: #94a3b8 !important;
        border-radius: 0.25rem !important;
    }

    /* 8. Pestañas Superiores (Tabs) */
    .fi-tabs {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        padding: 0.25rem !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }

    .fi-tabs-item button {
        color: #475569 !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        border-radius: 0.375rem !important;
        padding: 0.4rem 0.85rem !important;
        transition: all 0.15s ease !important;
    }

    .fi-tabs-item button:hover {
        color: #0b1031 !important;
        background-color: #f1f5f9 !important;
    }

    .fi-tabs-item-active button {
        background-color: #0b1031 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .fi-tabs-item-active button span {
        color: #ffffff !important;
    }

    .fi-tabs-item-active .fi-badge {
        background-color: #b48d56 !important;
        color: #ffffff !important;
    }

    /* 9. Formularios (Labels e Inputs) */
    .fi-fo-field-wrp-label label,
    .fi-fo-field-wrp-label span {
        color: #0b1031 !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #b48d56 !important;
        box-shadow: 0 0 0 2px rgba(180, 141, 86, 0.25) !important;
    }

    /* 10. Botones de Acción Primarios */
    .fi-btn-primary {
        background-color: #b48d56 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-radius: 0.5rem !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 1px 3px rgba(180, 141, 86, 0.3) !important;
    }

    .fi-btn-primary:hover {
        background-color: #9a7645 !important;
        box-shadow: 0 3px 6px rgba(180, 141, 86, 0.4) !important;
    }
</style>
