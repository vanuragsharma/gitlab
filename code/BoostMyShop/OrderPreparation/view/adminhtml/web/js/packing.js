/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "prototype",
    "Magento_Ui/js/modal/alert",
    'Magento_Ui/js/modal/confirm',
    "domReady!",
    "mage/translate"
], function ($, jUi, prototype, alert, confirm) {
    'use strict';

    $.Packing = function()
    {
        this.KC_value = '';
    };

    $.Packing.prototype = {


        ping: function() {
            alert('pong');
        },

        init: function (eSelectOrderByIdUrl, eItemIds, eOrderDetails, eMode, autoDownloadUrls, eAllowPartialPacking, eSaveItemUrl, eItemCustomOptionsFormUrl, ePackOrderByProducBarcode, eLargeOrderMode, eSelectBatchByIdUrl="", eisBatchEnable= 0, ebatchJson = [], eAutoCommit)
        {
            this.selectOrderByIdUrl = eSelectOrderByIdUrl;
            this.itemIds = eItemIds;
            this.OrderDetails = eOrderDetails;
            this.mode = eMode;
            this.allowPartialPacking = eAllowPartialPacking;
            this.popup = null;
            this.saveItemUrl = eSaveItemUrl;
            this.itemCustomOptionsFormUrl = eItemCustomOptionsFormUrl;
            this.packOrderByProducBarcode = ePackOrderByProducBarcode;
            this.largeOrderMode = eLargeOrderMode;
            this.BoxNo = 1;
            this.selectBatchByIdUrl= eSelectBatchByIdUrl;
            this.isBatchEnable = eisBatchEnable;
            this.batchJson = ebatchJson;
            this.isAutoCommit = eAutoCommit;

            $(document).on('keypress', {obj: this}, this.handleKey);
            $('#select-order').on('change', {obj: this}, this.selectOrderFromMenu);
            if(this.isBatchEnable == 1)
                $('#select-batch').on('change', {obj: this}, this.selectBatchFromMenu);

            this.updateStatuses();

            if (autoDownloadUrls)
                this.download(autoDownloadUrls);

            return this;
        },

        download: function (autoDownloadUrls) {
            autoDownloadUrls.forEach(function(url) {
                if (url) {
                    $('<iframe src="' + url + '" frameborder="0" scrolling="no" style="display: none;"></iframe>').appendTo('#iframe-container');
                }
            });
        },

        //********************************************************************* *************************************************************
        //
        selectBatchFromMenu: function (evt) {
            var url = evt.data.obj.selectBatchByIdUrl;
            var batchId = $('#select-batch option:selected').val();
            if (batchId)
            {
                url = url.replace('param_batch_id', batchId);
                document.location.href = url;
            }
        },

        //********************************************************************* *************************************************************
        //
        selectOrderFromMenu: function (evt) {
            var url = evt.data.obj.selectOrderByIdUrl;
            var orderInProgressId = $('#select-order option:selected').val();
            if (orderInProgressId)
            {
                url = url.replace('param_order_id', orderInProgressId);
                document.location.href = url;
            }
        },

        //********************************************************************* *************************************************************
        //
        waitForScan: function () {
            $('#div_product').hide();

            if (this.mode == 'search_batch')
                this.showInstruction($.mage.__('Scan batch reference'), false);
            else if (this.mode == 'pack_order') {
                this.showInstruction($.mage.__('Scan product barcode'), false);
                var urlParams = new URLSearchParams(location.search);
                if(this.packOrderByProducBarcode == '1' && urlParams.get('productbarcode')) {
                    this.KC_value = urlParams.get('productbarcode');
                    this.scanProduct();
                }
            }
            else
                this.showInstruction($.mage.__('Scan order barcode'), false);
        },


        //**********************************************************************************************************************************
        //
        handleKey: function (evt) {

            //Dont process event if focuses control is text
            var focusedElt = evt.target.tagName.toLowerCase();
            if ((focusedElt == 'text') || (focusedElt == 'textarea') || (focusedElt == 'input'))
                return true;

            var keyCode = evt.which;
            if (keyCode != 13) {
                evt.data.obj.KC_value += String.fromCharCode(keyCode);
                evt.data.obj.barcodeDigitScanned();
            }
            else {
                if (evt.data.obj.mode == 'search_batch')
                    evt.data.obj.scanBatch();
                else if (evt.data.obj.mode == 'pack_order')
                    evt.data.obj.scanProduct();
                else
                    evt.data.obj.scanOrder();
                evt.data.obj.KC_value = '';
            }

            return false;
        },

        //**********************************************************************************************************************************
        //Quantity buttons
        qtyMin: function(itemId)
        {
            $('#qty_packed_' + itemId).val(0);
            this.updateStatuses();
        },
        qtyMax: function(itemId)
        {
            $('#qty_packed_' + itemId).val($('#qty_to_ship_' + itemId).val());
            this.updateStatuses();
        },
        qtyDecrement: function(itemId)
        {
            if ($('#qty_packed_' + itemId).val() > 0)
                $('#qty_packed_' + itemId).val(parseInt($('#qty_packed_' + itemId).val()) - 1);
            this.updateStatuses();
        },
        qtyIncrement: function(itemId)
        {
            if (parseInt($('#qty_packed_' + itemId).val()) < parseInt($('#qty_to_ship_' + itemId).val()))
                $('#qty_packed_' + itemId).val(parseInt($('#qty_packed_' + itemId).val()) + 1);
            if(this.largeOrderMode == 1)
                $('#qty_scanned_' + itemId).html($('#qty_packed_' + itemId).val());
            this.updateStatuses();
        },
        qtyMinAll: function()
        {
            for (var key in this.itemIds) {
                var itemId = this.itemIds[key];
                if (itemId > 0) {
                    $('#qty_packed_' + itemId).val("0");
                }
            }
            this.updateStatuses();
        },
        qtyMaxAll: function()
        {
            for (var key in this.itemIds) {
                var itemId = this.itemIds[key];
                if (itemId > 0) {
                    var qtyToShip = $('#qty_to_ship_' + itemId).val();
                    $('#qty_packed_' + itemId).val(qtyToShip);
                }
            }
            this.updateStatuses();
        },

        //**********************************************************************************************************************************
        //
        updateStatuses: function()
        {
            var largeOrderMode = this.largeOrderMode;
            var isAutoCommit = this.isAutoCommit;
            var totalItem = this.itemIds.length;
            var itemAllQtyPacked = 0;
            this.itemIds.forEach(function(itemId) {
                var qtyPacked = $('#qty_packed_' + itemId).val();
                var qtyToShip = $('#qty_to_ship_' + itemId).val();
                var classes = '';
                var title = '';
                if (parseInt(qtyPacked) < parseInt(qtyToShip)) {
                    classes = 'packing-status-partial';
                    title = (qtyToShip - qtyPacked) + ' missing';
                }
                if (parseInt(qtyToShip) == parseInt(qtyPacked)) {
                    classes = 'packing-status-ok';
                    title = $.mage.__('Packed');
                    itemAllQtyPacked++;
                }
                if (parseInt(qtyPacked) == 0) {
                    classes = 'packing-status-none';
                    title= $.mage.__('Not packed');
                }
                if(largeOrderMode == 1){
                    $('#product_' + itemId).attr('class', "col-md-2 large-product-content-col" + " " + classes);
                }else{
                    $('#status_' + itemId).attr('class', "packing-status" + " " + classes);
                    $('#status_' + itemId).html(title);
                }
            });
            // All item Packed and autoCommit enable
            if(this.mode == 'pack_order' && itemAllQtyPacked == totalItem && isAutoCommit==1) {
                packingObj.commitPacking();
            }
        },

        //**********************************************************************************************************************************
        //
        scanOrder: function(){
            var orderIncrementId = this.KC_value;
            this.KC_value = '';

            var orderInProgressId = '';
            for (var key in this.OrderDetails) {
                if (this.OrderDetails[key][0] == orderIncrementId)
                    orderInProgressId = key;
            }

            if (!orderInProgressId){
                if(this.packOrderByProducBarcode == '1'){
                    this.findOrderByProductBarcode(orderIncrementId);
                }else{
                    this.showMessage($.mage.__('This order is not available'), true);
                }
            }
            else
            {
                var url = this.selectOrderByIdUrl;
                url = url.replace('param_order_id', orderInProgressId);
                document.location.href = url;
            }
        },

        //**********************************************************************************************************************************
        //
        findOrderByProductBarcode: function (barcode) {
            var orderInProgressId = '';
            for (var key in this.OrderDetails) {
                if (this.OrderDetails[key][1] == 'new')
                {
                    for (var barcodeIndex in this.OrderDetails[key][2]) {
                        if (this.barcodeMatch(barcode, this.OrderDetails[key][2][barcodeIndex]))
                            orderInProgressId = key;
                    }
                }
            }
            if(!orderInProgressId){
                this.showMessage($.mage.__('This order is not available'), true);
            }else{
                var url = this.selectOrderByIdUrl;
                url = url.replace('param_order_id', orderInProgressId);
                document.location.href = url+'?productbarcode='+barcode;
            }
        },

        //**********************************************************************************************************************************
        //
        barcodeMatch: function(barcode1, barcode2) {
            if ((typeof barcode1 === 'string' || barcode1 instanceof String) && (typeof barcode2 === 'string' || barcode2 instanceof String))
            {
                barcode1 = barcode1.replace(/^0+/, "");
                barcode2 = barcode2.replace(/^0+/, "");
                return (barcode1 == barcode2);
            }
        },

        //**********************************************************************************************************************************
        //
        scanBatch: function () {
            var batchRef = this.KC_value;
            this.KC_value = '';

            var batchId = '';
            for (var key in this.batchJson) {
                if (this.batchJson[key] == batchRef)
                    batchId = key;
            }

            if (!batchId){
                this.showMessage($.mage.__('This batch is not available'), true);
            }
            else
            {
                var url = this.selectBatchByIdUrl;
                url = url.replace('param_batch_id', batchId);
                document.location.href = url;
            }

        },
        //**********************************************************************************************************************************
        //
        scanProduct: function () {

            var barcode = this.KC_value;
            this.KC_value = '';

            if (barcode == 'commit')
            {
                packingObj.commitPacking();
                return;
            }

            if (barcode == 'cancel')
            {
                document.location.href = document.location.href;
                return;
            }

            //prevent to scan product without barcode
            if (barcode == '')
                return;

            //scale management
            if(barcode.substring(0, 5) == "SCALE"){
                var weight = barcode.substring(5);
                $('#total_weight').val(weight);
                this.playOk();
                return;
            }
            
            //check barcode
            var itemId = this.getItemIdFromBarcode(barcode);
            if (!itemId)
            {
                this.showMessage($.mage.__('Incorrect Product Barcode'), true);
                return false;
            }

            //check quantity
            var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
            if (remainingQuantity == 0)
            {
                this.showMessage($.mage.__('Product already packed !'), true);
                return false;
            }

            this.playOk();
            this.qtyIncrement(itemId);

        },

        //******************************************************************************
        //
        commitPacking: function() {

            if (!this.isCompletelyPacked() && !this.allowPartialPacking)
            {
                this.showMessage($.mage.__('Packing is not complete, please pack all products !'), true);
                return false;
            }

            if (!this.isCompletelyPacked() && this.allowPartialPacking)
            {
                confirm({
                    content: $.mage.__('Packing is not complete, do you really want to confirm a partial shipment ?'),
                    actions: {
                        confirm: function () {
                            jQuery('#frm_products').submit();
                        }
                    }
                });
                return false;
            }

            jQuery('#frm_products').submit();

        },


        //******************************************************************************
        //
        isCompletelyPacked: function() {
            for (var key in this.itemIds) {
                var itemId = this.itemIds[key];
                if (itemId > 0) {
                    var qtyPacked = $('#qty_packed_' + itemId).val();
                    var qtyToShip = $('#qty_to_ship_' + itemId).val();
                    if (qtyPacked < qtyToShip)
                        return false;
                }
            }
            return true;
        },

        //******************************************************************************
        //
        deleteShippingLabel: function(deleteShippingLabelUrl) {
            var data = {
                FORM_KEY: window.FORM_KEY
            };

            confirm({
                content: $.mage.__('Do you really want to delete the pre-generated label ?'),
                actions: {
                    confirm: function () {
                        jQuery.ajax({
                            url: deleteShippingLabelUrl,
                            data: data,
                            success: function () {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        },

        //**********************************************************************************************************************************
        //
        getItemIdFromBarcode: function(barcode){
            var itemId = false;
            for (var key in this.itemIds) {
                //exact match for barcode

                if ((this.itemIds.hasOwnProperty(key)) && ($('#barcode_' + this.itemIds[key]).val() == barcode))
                {

                    //if item has qty to pack, return it directly, else store it in item variable to potentially find another item with same barcode and qty available
                    itemId = this.itemIds[key];
                    var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
                    if (remainingQuantity > 0)
                        return itemId;
                }

                // check for additional barcode
                if((this.itemIds.hasOwnProperty(key)) && ($('#additional_barcodes_' + this.itemIds[key]).val())){
                    if(this.matchForBarcodes($('#additional_barcodes_' + this.itemIds[key]).val(),barcode)) {
                        itemId = this.itemIds[key];
                        var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
                        if (remainingQuantity > 0)
                            return itemId;
                    }
                }

                //exact match for sku
                if ((this.itemIds.hasOwnProperty(key)) && ($('#div_sku_' + this.itemIds[key]).html() == barcode))
                {
                    //if item has qty to pack, return it directly, else store it in item variable to potentially find another item with same barcode and qty available
                    itemId = this.itemIds[key];
                    var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
                    if (remainingQuantity > 0)
                        return itemId;
                }

                //add leading "0"
                if ((this.itemIds.hasOwnProperty(key)) && ($('#barcode_' + this.itemIds[key]).val() == ("0" + barcode)))
                {
                    //if item has qty to pack, return it directly, else store it in item variable to potentially find another item with same barcode and qty available
                    itemId = this.itemIds[key];
                    var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
                    if (remainingQuantity > 0)
                        return itemId;
                }

                //remove leading "0"
                if (barcode.charAt(0) == "0")
                {
                    if ((this.itemIds.hasOwnProperty(key)) && ($('#barcode_' + this.itemIds[key]).val() == barcode.substr(1)))
                    {
                        //if item has qty to pack, return it directly, else store it in item variable to potentially find another item with same barcode and qty available
                        itemId = this.itemIds[key];
                        var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
                        if (remainingQuantity > 0)
                            return itemId;
                    }
                }

            }
            return itemId;
        },
        matchForBarcodes:function(items,barcode){
            var matched = false;
            jQuery.each(JSON.parse(atob(items)), function (key, data) {
                if (data == barcode){
                    matched = true;
                }
            });
            return matched;
        },

        //**********************************************************************************************************************************
        //
        barcodeDigitScanned: function () {
            this.showMessage(this.KC_value);
        },

        editShippingMethod: function(url)
        {
            this.popup = jQuery('#edit_popup').modal({
                title: jQuery.mage.__('Changes shipping method'),
                type: 'slide',
                closeExisting: false,
                buttons: []
            });

            var data = {
                FORM_KEY: window.FORM_KEY
            };

            jQuery.ajax({
                url: url,
                data: data,
                success: function (resp) {
                    jQuery('#edit_popup').html(resp +'<div id="change_shipping_methods_page_tabs"></div>');
                    packingObj.popup.modal('openModal');
                }
            });
        },

        //**********************************************************************************************************************************
        //
        editOrderItem: function(url) {

            this.popup = jQuery('#edit_popup').modal({
                title: jQuery.mage.__('Edit order item'),
                type: 'slide',
                closeExisting: false,
                buttons: []
            });

            var data = {
                FORM_KEY: window.FORM_KEY
            };

            jQuery.ajax({
                url: url,
                data: data,
                success: function (resp) {
                    jQuery('#edit_popup').html(resp);
                    packingObj.popup.modal('openModal');
                }
            });
        },

        saveOrderItem: function(itemId)
        {
            var data = $('#frm_edit_item').serialize();

            jQuery.ajax({
                url: packingObj.saveItemUrl,
                data: data,
                dataType: 'json',
                success: function (resp) {
                    if (!resp.success) {
                        alert({content: resp.message});
                    }
                    else
                    {
                        packingObj.popup.modal('closeModal');
                        $('#qty_to_ship_' + resp.in_progress_item.ipi_id).val(resp.in_progress_item.ipi_qty);
                        $('#div_qty_to_ship_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.ipi_qty;
                        $('#div_sku_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.product.sku;
                        $('#div_name_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.product.name + '<br>' + resp.in_progress_item.product.options;
                        $('#div_image_' + resp.in_progress_item.ipi_id).attr('src', resp.in_progress_item.product.image);
                        $('#div_location_' + resp.in_progress_item.ipi_id).innerHTML = resp.in_progress_item.product.location;
                        alert({content: resp.message});
                    }
                },
                failure: function (resp) {
                    //jQuery('#debug').html('An error occured.');
                }
            });
        },

        decreaseOrderItemQty: function()
        {
            var currentValue = parseInt($('#edit_item_new_qty').val());
            if (currentValue > 0)
                currentValue -= 1;
            jQuery('#edit_item_new_qty').val(currentValue);
            jQuery('#div_item_edit_qty')[0].innerHTML = currentValue;
        },

        increaseOrderItemQty: function(searchString)
        {
            var currentValue = parseInt($('#edit_item_new_qty').val());
            currentValue += 1;
            jQuery('#edit_item_new_qty').val(currentValue);
            jQuery('#div_item_edit_qty')[0].innerHTML = currentValue;
        },

        selectSubstitutionProduct: function(productId, sku, name)
        {
            jQuery('#div_substitution_product_description')[0].innerHTML = '"' + sku + ' - ' + name + '"';
            jQuery('#edit_item_new_sku').val(sku);

            var data = {};
            data.FORM_KEY = window.FORM_KEY;
            data.product_id = productId;

            jQuery.ajax({
                url: packingObj.itemCustomOptionsFormUrl,
                data: data,
                dataType: 'json',
                success: function (resp) {
                    if (!resp.success) {
                        alert({content: resp.message});
                    }
                    else
                    {
                        $('#substitution_options')[0].innerHTML = resp.html;
                    }
                },
                failure: function (resp) {
                    //jQuery('#debug').html('An error occured.');
                }
            });
        },


        //******************************************************************************
        //
        showMessage: function (text, error) {
            if (text == '')
                text = '&nbsp;';

            if (error)
                text = '<font color="red">' + text + '</font>';
            else
                text = '<font color="green">' + text + '</font>';

            $('#div_message').html(text);
            $('#div_message').show();

            if (error)
                this.playNok();

        },

        //******************************************************************************
        //
        hideMessage: function () {
            $('#div_message').hide();
        },


        //******************************************************************************
        //display instruction for current
        showInstruction: function (text) {
            $('#div_instruction').html(text);
            $('#div_instruction').show();
        },

        //******************************************************************************
        //
        hideInstruction: function () {
            $('#div_instruction').hide();
        },

        playOk: function()
        {
            var playPromise = $("#audio_ok").get(0).play();
            if (playPromise !== undefined) {
                playPromise.then(_ => {
                    // Automatic playback started!
                    // Show playing UI.
                })
                    .catch(error => {
                        // Auto-play was prevented
                        // Show paused UI.
                    });
            }
        },

        playNok: function ()
        {
            $("#audio_nok").get(0).play();
        },

        //**********************************************************************************************************************************
        //

        editShipmentWeight: function(url)
        {
            this.popup = jQuery('#edit_popup').modal({
                title: jQuery.mage.__('Edit boxes'),
                type: 'slide',
                closeExisting: false,
                buttons: []
            });

            var data = {
                FORM_KEY: window.FORM_KEY
            };

            jQuery.ajax({
                url: url,
                data: data,
                success: function (resp) {
                    jQuery('#edit_popup').html(resp);
                    packingObj.popup.modal('openModal');
                }
            });
        },

        //**********************************************************************************************************************************

        addParcelBoxes: function()
        {
            this.addParcelCount();
            this.BoxNo += 1;
            var boxId = this.BoxNo;
            var markup =
            "<tr>" +
            "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'> #Box: "+boxId+
            "</th>"+
            "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
            "Total weight : "+
            "<input type=\"text\" size=\"5\" value=\"\" style=\"color: black;\" id=\"total_weight"+boxId+"\" name=\"boxes["+boxId+"][total_weight]\">"+
            "</th>"+
            "<input type=\"hidden\"  value=\"1\"  id=\"parcel_count"+boxId+"\" name=\"boxes["+boxId+"][parcel_count]\">"+
            "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
            "Length : "+
            "<input type=\"text\" size=\"3\" value=\"\" style=\"color: black;\" id=\"parcel_length"+boxId+"\" name=\"boxes["+boxId+"][parcel_length]\">"+
            "</th>"+
            "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
            "Width : "+
            "<input type=\"text\" size=\"3\" value=\"\" style=\"color: black;\" id=\"parcel_width"+boxId+"\" name=\"boxes["+boxId+"][parcel_width]\">"+
            "</th>"+
            "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
            "Height : "+
            "<input type=\"text\" size=\"3\" value=\"\" style=\"color: black;\" id=\"parcel_height"+boxId+"\" name=\"boxes["+boxId+"][parcel_height]\">"+
            "</th>"+
            "<td align=\"center\">"+
            "<input type=\"button\" value=\"-\" onclick=\"packingObj.removeParcelBox(this)\">"+
            "</td></tr>";
            jQuery("#parcelbox").append(markup);
        },

        showBoxes: function(boxes)
        {
            jQuery.each( boxes, function( key, box ) {
                var weight = box.total_weight;
                var height = box.parcel_height;
                var width = box.parcel_width;
                var length = box.parcel_length;
                packingObj.addParcelCount();
                packingObj.BoxNo += 1;
                var boxId = packingObj.BoxNo;
                var markup =
                    "<tr>" +
                    "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                    "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'> #Box: "+boxId+
                    "</th>"+
                    "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                    "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
                    "Total weight : "+
                    "<input type=\"text\" size=\"5\" value=\""+weight+"\" style=\"color: black;\" id=\"total_weight"+boxId+"\" name=\"boxes["+boxId+"][total_weight]\">"+
                    "</th>"+
                    "<input type=\"hidden\"  value=\"1\"  id=\"parcel_count"+boxId+"\" name=\"boxes["+boxId+"][parcel_count]\">"+
                    "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                    "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
                    "Length : "+
                    "<input type=\"text\" size=\"3\" value=\""+length+"\" style=\"color: black;\" id=\"parcel_length"+boxId+"\" name=\"boxes["+boxId+"][parcel_length]\">"+
                    "</th>"+
                    "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                    "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
                    "Width : "+
                    "<input type=\"text\" size=\"3\" value=\""+width+"\" style=\"color: black;\" id=\"parcel_width"+boxId+"\" name=\"boxes["+boxId+"][parcel_width]\">"+
                    "</th>"+
                    "<th style='background-image: linear-gradient(173deg, #183895, #00abeb) !important;\n" +
                    "background-image: -webkit-linear-gradient(173deg, #183895, #00abeb) !important; color:white !important;'>"+
                    "Height : "+
                    "<input type=\"text\" size=\"3\" value=\""+height+"\" style=\"color: black;\" id=\"parcel_height"+boxId+"\" name=\"boxes["+boxId+"][parcel_height]\">"+
                    "</th>"+
                    "<td align=\"center\">"+
                    "<input type=\"button\" value=\"-\" onclick=\"packingObj.removeParcelBox(this)\">"+
                    "</td></tr>";

                jQuery("#parcelbox").append(markup);
            });
        },
        removeParcelBox: function(element)
        {
            this.subsParcelCount();
            jQuery(element).parents('tr').remove();
        },
        addParcelCount: function () {
            var parcel_count = parseInt(jQuery("#parcel_count").val());
            parcel_count += 1;
            jQuery("#parcel_count").val(parcel_count);
        },
        subsParcelCount: function () {
            var parcel_count = parseInt(jQuery("#parcel_count").val());
            parcel_count -= 1;
            jQuery("#parcel_count").val(parcel_count);
        }
    }

    return new $.Packing();

});
