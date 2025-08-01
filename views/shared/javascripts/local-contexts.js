if (!Omeka) {
    var Omeka = {};
}

Omeka.LocalContexts = {};

(function ($) {
    /**
     * Add link that collapses and expands content.
     */
    Omeka.LocalContexts.manageDrawerToggleLabels = function () {
        $('.drawer')
            .on('omeka:toggle-drawer', '.drawer-toggle', function (event) {
                var toggleButton = $(this);
                var expandLabel = toggleButton.data('expand-label');
                var collapseLabel = toggleButton.data('collapse-label');
                if (toggleButton.attr('aria-expanded') == 'true') {
                    toggleButton.attr('aria-label', collapseLabel);
                } else {
                    toggleButton.attr('aria-label', expandLabel);
                }
            });
    };
})(jQuery);
