(function(){
    setTimeout(function(){
        tinyMCE.init({
            selector: 'textarea',
            plugins: 'code, lists',
            toolbar: [
                {
                    name: 'history',
                    items: ['undo', 'redo']
                },
                {
                    name: 'formatting',
                    items: ['bullist', 'bold', 'italic', 'underline']
                },
                {
                    name: 'alignment', 
                    items: [ 'alignleft', 'aligncenter', 'alignright' ]
                },
                {
                    name: 'indentation', 
                    items: [ 'outdent', 'indent' ]
                }
            ]
        });
    }, 1000);

    jQuery(function($) {
        // init
        if(parseInt($('[name="show_banner_above_hero"]:checked').val()) === 0) {
            $('[name="show_banner_above_hero"]').closest('tr').nextAll('tr:lt(4)').hide();
        }

        $('[name="show_banner_above_hero"]').on('change', function(evt) {
            var $radio = $(evt.currentTarget);

            if(parseInt($radio.val()) === 0) {
                $radio.closest('tr').nextAll('tr:lt(4)').hide();
            } else {
                $radio.closest('tr').nextAll('tr:lt(4)').show();
            }
        });
    });
})();
