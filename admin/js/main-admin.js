(function( $) {
    'use strict';
    $(document).ready(function() {
        $('.button-generate-posts').click(function () {
            $('.spinner').addClass('is-active');
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    'action' : 'start_parse_file',
                },
                success:function(data) {
                    $('.spinner').removeClass('is-active');
                    console.log(data);
                },
                error: function(errorThrown){
                    $('.spinner').removeClass('is-active');
                    console.log(errorThrown);
                }
            });
        });
    });
})(jQuery);