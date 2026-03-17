require(['jquery'], function ($) {
    $(document).ready(function(){
        $('#open-size-guide').click(function(){
            $('#size-guide-modal').fadeIn();
            var img = $('.size-guide-img');
            if(img.attr('src') === undefined){
                img.attr('src', img.data('src'));
            }
        });
        $('.size-guide-close, .size-guide-overlay').click(function(){
            $('#size-guide-modal').fadeOut();
        });
        $('.size-guide-img').click(function(){
            $(this).toggleClass('zoom');
        });
    });
});
