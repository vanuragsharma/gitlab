require([
    'jquery','Magento_Ui/js/modal/modal', 'Magento_Ui/js/modal/confirm', 'mage/url'
], function ($,modal, confirmation, urlBuilder) {
    'use strict';

    jQuery(document).on("click",".hold_order",function(e){
        e.preventDefault();
        if(!jQuery("#hold-order-popup-modal").length)
            jQuery('#html-body').append('<div id="hold-order-popup-modal" ></div>');
        var link = jQuery(this);
        var url = jQuery('#hold-url').data("hold-url");
        var progressUrl = jQuery('#hold-url').data("inprogress-url");
        if(url == null){
            url = link.attr("href");
        }
        var urlsplit = url.split('/');
        var $idIndex = jQuery.inArray( "order_id", urlsplit) + 1;
        var order_id = urlsplit[$idIndex];
        var param = {form_key:window.FORM_KEY,order_id:order_id}
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
                    title: data.title,
                    buttons: []
                };
                var popup = modal(options, $('#hold-order-popup-modal'));
                jQuery("#hold-order-popup-modal").html(data.content);
                jQuery("#hold-order-popup-modal").modal("openModal");

                jQuery(document).on("click","#hold",function(e){
                    var url = jQuery("#hold").data("hold-url");
                    var note = jQuery("#hold_order_note").val();
                    var param = {form_key:window.FORM_KEY,order_id:order_id,note:note};
                    jQuery.ajax({
                        url: url,
                        data: param,
                        type: "POST",
                        success: function(data, status, xhr) {
                            var options = {
                                type: 'slide',
                                responsive: true,
                                innerScroll: true,
                                title: data.title,
                                buttons: []
                            };
                            jQuery("#hold-order-popup-modal").modal("closeModal");
                            if(typeof tab_in_progressJsObject != "undefined"){
                                tab_in_progressJsObject.resetFilter();
                            }
                            if(progressUrl){
                                window.location.replace(progressUrl);
                            }
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
