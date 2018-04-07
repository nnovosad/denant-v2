jQuery( document ).ready(function() {
    jQuery( ".nav > li" ).click(function() {
        jQuery( ".nav > li > a" ).removeClass('active-item');
        jQuery(this).children().addClass('active-item');
    });
});