require([
    'jquery','Magento_Ui/js/modal/modal', 'Magento_Ui/js/modal/confirm', 'mage/url'
], function ($,modal, confirmation, urlBuilder) {
    'use strict';

    jQuery(document).on("click","a.batch_detail_popup",function(e){
        e.preventDefault();
        //function showbatchpopup(el, bob_id, bob_label)
        //{
        var link = jQuery(this);
        var url = link.attr("data-href");
        var bob_id = link.attr("data-id");
        var bob_label = link.attr("data-label");

        if(!jQuery(".op-batch-popup-modal").length)
            jQuery('#html-body').append('<div class="op-batch-popup-modal" id = "op-batch-popup-modal"></div>');

        var param = {form_key:window.FORM_KEY,bob_id:bob_id}

        jQuery.ajax({
            showLoader: true,
            url: url,
            data: param,
            type: "POST",
            success: function(data, status, xhr) {
                var options = {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    title: "Batch #"+bob_label,
                    buttons: []
                };
                var popup = modal(options, $('#op-batch-popup-modal'));
                jQuery("#op-batch-popup-modal").html(data.content+'<div id="batch_tab_content" class="dashboard-store-stats-content"></div>');
                jQuery("#op-batch-popup-modal").modal("openModal");
            },
            error: function (xhr, status, errorThrown) {
                if(xhr.responseText != '' || xhr.responseText !== undefined)
                {
                    alert('Error : '+jQuery.parseJSON(xhr.responseText).message);
                }
                else{
                    alert('Error happens. Try again.');
                }
            }
        });
    });
});
