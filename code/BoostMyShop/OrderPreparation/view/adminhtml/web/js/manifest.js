require([
    'jquery','Magento_Ui/js/modal/modal', 'Magento_Ui/js/modal/confirm', 'mage/url', "mage/calendar"
], function ($, modal, confirmation, urlBuilder, calendar) {
    'use strict';

    $("#calendar_inputField").calendar({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        buttonText: 'Select Date'
    });

    var myDate = new Date();
    myDate.setDate(myDate.getDate() - 3);
    $('#calendar_inputField').datepicker('setDate', myDate);

    jQuery(document).on("click","#search_shipments",function(e){
        e.preventDefault();
        var carrier = jQuery('#field-carrier').val();
        var warehouseId = jQuery('#field-warehouse').val();
        var from_date = jQuery('#calendar_inputField').val();
        var url = jQuery("#search_shipments").data("search-shipments-url");
       if(!carrier.length || !warehouseId.length || !from_date.length){
           alert('Please select all required fields.');
           return false;
       }

        jQuery.ajax({
            showLoader: true,
            url: url,
            data: {carrier:carrier,warehouseId:warehouseId, from_date:from_date},
            type: "POST",
            success: function(data, status, xhr) {
                jQuery("#result-shipments").html(data.content);
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

    jQuery(document).on("click","a.manifest_detail_popup",function(e){
        e.preventDefault();
        var link = jQuery(this);
        var url = link.attr("data-href");
        var bom_id = link.attr("data-id");

        if(!jQuery(".op-manifest-popup-modal").length)
            jQuery('#html-body').append('<div class="op-manifest-popup-modal" id = "op-manifest-popup-modal"></div>');

        var param = {form_key:window.FORM_KEY,bom_id:bom_id};

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
                    title: "Manifest details",
                    buttons: []
                };
                var popup = modal(options, $('#op-manifest-popup-modal'));
                jQuery("#op-manifest-popup-modal").html(data.content+'<div id="grid_tab_content" class="dashboard-store-stats-content"></div>');
                jQuery("#op-manifest-popup-modal").modal("openModal");
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

    jQuery(document).on("click",".manifest_export",function(e){
        e.preventDefault();
        var link = jQuery(this);
        var carrierId = link.attr("data-carrier-id");
        var manifestId = link.attr("data-manifest-id");
        var url = link.attr("data-manifest-export-url");

        var param = {form_key:window.FORM_KEY,carrierId:carrierId, manifestId:manifestId};

        jQuery.ajax({
            showLoader: true,
            url: url,
            data: param,
            type: "POST",
            success: function(data, status, xhr) {
                jQuery("#manifest_export_"+manifestId).val(data.text);
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