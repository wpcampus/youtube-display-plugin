jQuery(document).ready(function(){
    jQuery('.vid').each(function(){
        var image = jQuery(this).find('img');
        console.log(image);
        console.log(image.height());
        jQuery(this).find('.play').css("border-top-width", image.height()*.1);
        jQuery(this).find('.play').css("border-bottom-width", image.height()*.1);
        jQuery(this).find('.play').css("border-left-width", image.height()*.20);
        jQuery(this).find('.play').css("top", image.height()*.30);
        jQuery(this).find('.vid-caption').css("font-size", image.height()*.08);
    });

    jQuery('.popup-youtube').magnificPopup({
        disableOn: 700,
        type: 'iframe',
        mainClass: 'mfp-fade',
        removalDelay: 160,
        preloader: false,

        fixedContentPos: false
    });
});
