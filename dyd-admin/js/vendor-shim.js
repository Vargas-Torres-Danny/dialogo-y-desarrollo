/**
 * Vendor shim
 * La plantilla original carga los plugins "widgster" y "hammer.js" desde
 * node_modules (no incluidos en este paquete estático). Como no usamos las
 * funciones de cerrar/arrastrar widgets ni gestos táctiles en este panel,
 * este shim evita errores de JavaScript sin afectar ninguna funcionalidad
 * que sí usamos (sidebar, dropdowns, modales, tablas, tooltips).
 */
(function ($) {
    if (!$.fn.widgster) {
        $.fn.widgster = function () { return this; };
        $.fn.widgster.Constructor = { DEFAULTS: {} };
    }
    if (!$.fn.hammer) {
        $.fn.hammer = function () {
            return { bind: function () { return this; } };
        };
    }
})(jQuery);
