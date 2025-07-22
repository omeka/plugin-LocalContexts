if (!Omeka) {
    var Omeka = {};
}

Omeka.LocalContexts = {};

(function ($) {
    /**
     * Add link that collapses and expands content.
     */
    Omeka.LocalContexts.addHideButtons = function () {
        $('.drawer-contents').each(function () {
            $(this).hide();
        });
        $('.drawer-toggle')
            .click(function (event) {
                event.preventDefault();
                $(this).parent().siblings('.drawer-contents').toggle();
                $(this).toggleClass('opened');
            })
            .mousedown(function (event) {
                event.stopPropagation();
            });
    };
})(jQuery);
