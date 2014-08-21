(function($){GGPROptions;
    $(document).ready(function(){
        $('#ggpr_used').click(function(){
            if($(this).is(':checked')){
                $('#ggpr_not_used').prop( "checked", false );
            }
        });
        $('#ggpr_not_used').click(function(){
            if($(this).is(':checked')){
                $('#ggpr_used').prop( "checked", false );
            }
        });
        $('.ggpr-search-form').submit(function(e){
            
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
                alert(GGPROptions.admin_empty_sf);
                e.preventDefault();
            }
        });
        $('.ggpr-edit-form').submit(function(e){
            if(!window.confirm(GGPROptions.admin_confirm_text)){
                e.preventDefault();
                return false;
            }
        });
    });
})(jQuery);
