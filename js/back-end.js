(function($){GGPROptions;
    $(document).ready(function(){
        $('.ggpr-search-form').submit(function(e){
            //e.preventDefault();
            var submit = false,
                sel = '#ggpr_code, #ggpr_name, #ggpr_address, #ggpr_post_code, #ggpr_city, #ggpr_country, #ggpr_phone, #ggpr_email, #ggpr_invoice_no, #ggpr_supplier, #ggpr_dop, #ggpr_dor';
            $(sel).each(function(){
                if($(this).val() != ''){
                    submit = true;
                }
            });
            if($('#ggpr_used').is(':checked')){
                submit = true;
            }
            if(!submit){
                e.preventDefault();
            }
        });
    });
})(jQuery);
