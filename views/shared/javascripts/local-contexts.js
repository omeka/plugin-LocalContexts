if (!Omeka) {
    var Omeka = {};
}

Omeka.LocalContexts = {};

(function ($) {
    /**
     * Add link that collapses and expands content.
     */
    Omeka.LocalContexts.addHideButtons = function () {
        $('.lc-collapsible-content').each(function () {
            $(this).hide();
        });
        $('.lc-collapsible-title').each(function () {
            $(this).after('<div class="drawer-toggle"></div>');
        });
        $('.drawer-toggle')
            .click(function (event) {
                event.preventDefault();
                $(this).siblings('.lc-collapsible-content').toggle();
                $(this).toggleClass('opened');
            })
            .mousedown(function (event) {
                event.stopPropagation();
            });
    };
})(jQuery);
