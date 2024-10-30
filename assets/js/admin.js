var $ = jQuery;
$(document).ready(function () {

    checkCheckboxes();

    $(document).on('change', '#woocommerce_hkd_converse_save_card, #woocommerce_hkd_converse_testmode', function () {
        checkCheckboxes();
    });

    $(document).on('mouseover', '.wrap-converse .woocommerce-help-tip', function () {
        let parentId = $(this).parent().attr('for');
        console.log(parentId);
        if (parentId === 'woocommerce_hkd_converse_save_card_button_text') {
            $('#tiptip_content').css({
                'width': '300px'
            }).addClass('tiptip_content_changed_style').html('<img src="'+ myScriptConverse.pluginsUrl + 'assets/images/bindingnew.jpg" width="300">');
        } else if(parentId === 'woocommerce_hkd_converse_save_card_header') {
            $('#tiptip_content').css({
                'width': '300px'
            }).addClass('tiptip_content_changed_style').html('<img src="'+ myScriptConverse.pluginsUrl + 'assets/images/payment.jpg" width="300">');
        }else if(parentId === 'woocommerce_hkd_converse_save_card_use_new_card'){
            $('#tiptip_content').css({
                'width': '300px'
            }).addClass('tiptip_content_changed_style').html('<img src="'+ myScriptConverse.pluginsUrl + 'assets/images/newcard.jpg" width="300">');
        }else{
            $('#tiptip_content').removeClass('tiptip_content_changed_style').css({'max-width': '150px'});
        }
    });

    function checkCheckboxes() {
        $('.hiddenValueConverse').parents('tr').hide();
        let saveCardMode = $('#woocommerce_hkd_converse_save_card').is(':checked');
        let testMode = $('#woocommerce_hkd_converse_testmode').is(':checked');
        if (saveCardMode) {
            $('.saveCardInfoConverse').parents('tr').show();
        } else {
            $('.saveCardInfoConverse').parents('tr').hide();
        }

        if(testMode){
            $('.testModeInfoConverse').parents('tr').show();
        }else{
            $('.testModeInfoConverse').parents('tr').hide();
        }
    }
});
