(function($){GGPROptions;
    var fwo = '<input type="hidden" name="ggpr-action" value="check-only"/><div class="ggpr-field-wrap"><label>' + GGPROptions.regi_code + '</label><div class="ggpr-field">',
        fwc = '</div>';
    $(document).ready(function(){
        $('.ggpr-selector a').on('click', function(e){
            e.preventDefault();
            $('.ggpr-selected').removeClass('ggpr-selected');
            $(this).closest('li').addClass('ggpr-selected');
            
            var i=0,
                $form = $(this).closest('.ggpr-form-wrap').find('form'),
                fields = window.parseInt($(this).data('fields')),
                html = '';
            for(i=0;i<fields;i++){
                html = html + '<input class="ggpr-code-field" type="text" id="ggpr_product_regi_codes_' + i + '" name="ggpr_product_regi_codes[' + i + ']" value=""/>';
            }
            console.log(html);
            $form.find('.ggpr-form-inner').html(fwo+html+fwc);
            $form.show(300);
        });
    });
})(jQuery);
