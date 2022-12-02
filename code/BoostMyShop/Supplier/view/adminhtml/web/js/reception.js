
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "prototype",
    "Magento_Ui/js/modal/modal"
], function(jQuery, UI, translate, prototype, modal){

    window.AdminOrderReception = new Class.create();

    AdminOrderReception.prototype = {

        initialize: function(){

        },

        init : function(productIds, barcodes){
            this.productIds = productIds;
            this.barcodes = barcodes;
            this.barcodesToSave = new Array();

            this.afterProductScanCallback = null;
            this.afterFillAllQuantitiesCallback = null;

            this.barcodeToAssign = '';
            this.negativeQty = false;
            this.KC_value = '';
            jQuery(document).on('keypress', {obj: this}, this.handleKey);

            this.showMessage('Barcode scan enabled');
        },

        setAfterProductScanCallback: function(callBack)
        {
            this.afterProductScanCallback = callBack;
        },

        setAfterFillAllQuantitiesCallback: function(callBack)
        {
            this.afterFillAllQuantitiesCallback = callBack;
        },

        qtyChanged: function(){
            this.updateRowColors();
            this.updateSummary();
        },

        qtyMini: function(productId) {
            var receive = jQuery('#received_' + productId).val();
            jQuery('#qty_' + productId).val(0-receive);
            this.qtyChanged();
        },
        qtyMaxi: function(productId) {
            jQuery('#qty_' + productId).val(jQuery('#remaining_' + productId).val());
            this.qtyChanged();
        },
        qtyIncrease: function(productId) {
            jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);
            this.qtyChanged();
        },
        qtyDecrease: function(productId) {
            var allowedQty =  0 - parseInt(jQuery('#received_' + productId).val());
            if (jQuery('#qty_' + productId).val() > allowedQty)
                jQuery('#qty_' + productId).val(jQuery('#qty_' + productId).val() - 1);
            this.qtyChanged();
        },

        updateRowColors: function(){
            var i;
            for(i=0;i<this.productIds.length; i++)
            {
                var productId = this.productIds[i];
                this.isAllowed(productId);
                var qty = jQuery('#qty_' + productId).val();
                var delta = jQuery('#remaining_' + productId).val() - qty;

                var status;
                var color;
                if (delta == '0')
                {
                    status = 'OK';
                    color = '#01DF01';
                }
                if (delta > 0)
                {
                    status = delta + ' missing';
                    color = 'yellow';
                }
                if (delta < 0)
                {
                    status = (-delta) + ' over received';
                    color = 'red';
                }

                if(qty<0)
                {
                    status = (-qty) + ' canceled';
                    color = 'red';
                    this.negativeQty = true;
                }


                jQuery('#cell_status_' + productId).html(status);
                jQuery('#cell_status_' + productId).css('padding', 6);
                jQuery('#cell_status_' + productId).css('border-radius', '10px');
                jQuery('#cell_status_' + productId).css('font-weight', 'bold');
                jQuery('#cell_status_' + productId).css('background-color', color);
            }
        },

        fillAllQuantities: function()
        {
            var i;
            for(i=0;i<this.productIds.length; i++) {
                var productId = this.productIds[i];
                jQuery('#qty_' + productId).val(jQuery('#remaining_' + productId).val());
            }

            if (this.afterFillAllQuantitiesCallback != null)
            {
                eval(this.afterFillAllQuantitiesCallback + '()');
            }

            this.qtyChanged();
        },

        updateSummary: function()
        {
            var received = 0;
            var leftToReceive = 0;
            var overReceived = 0;

            var i;
            for(i=0;i<this.productIds.length; i++) {
                var productId = this.productIds[i];
                this.isAllowed(productId);
                var currentProductReceived = parseInt(jQuery('#qty_' + productId).val());
                var currentProductExpected = parseInt(jQuery('#remaining_' + productId).val());
                var currentProductRemaining = (currentProductExpected - currentProductReceived);

                if (currentProductRemaining < 0)
                    currentProductRemaining = 0;
                var currentProductOverReceived = 0;
                if (currentProductReceived > currentProductExpected)
                    currentProductOverReceived = currentProductReceived - currentProductExpected;

                received += currentProductReceived;
                leftToReceive += currentProductRemaining;
                overReceived += currentProductOverReceived;
            }

            jQuery('#summary_received').html(received);
            jQuery('#summary_left_to_receive').html(leftToReceive);
            jQuery('#summary_over_received').html(overReceived);
        },

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
                evt.data.obj.checkBarcode();
                evt.data.obj.KC_value = '';
            }

            return false;
        },

        barcodeDigitScanned: function () {
            this.showMessage('Barcode : ' + this.KC_value);
        },

        checkBarcode: function () {

            var barcode = this.KC_value;
            this.KC_value = '';

            var productId = this.getProductIdFromBarcode(barcode);
            if (!productId) {
                this.playNok();
                this.showBarcodeAssignmentWindow(barcode);
                return;
            }
            else
            {
                jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);

                var productName = jQuery('#name_' + productId).val();
                var imageUrl = jQuery('#image_' + productId).val();
                var msg = '';
                msg += '<img src="' + imageUrl + '" width="100" height="100"><br>';
                msg += '"' + productName + '" (' + jQuery('#qty_' + productId).val() + ' / ' + jQuery('#remaining_' + productId).val() + ')';
                this.showMessage(msg);
                this.qtyChanged();
                this.playOk();

                if (this.afterProductScanCallback != null)
                {
                    eval(this.afterProductScanCallback + '(' + productId + ')');
                }
            }
        },

        showBarcodeAssignmentWindow: function(barcode)
        {
            this.barcodeToAssign = barcode;
            jQuery('#div-barcode-to-assign').html(barcode);

            var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: []
                };

            var popup = modal(options, jQuery('#assign-barcode-modal'));
            jQuery('#assign-barcode-modal').modal('openModal');

        },

        assignBarcode: function(productId)
        {
            jQuery('#assign-barcode-modal').modal('closeModal');

            this.KC_value = this.barcodeToAssign;
            this.barcodes[this.barcodeToAssign] = productId;
            this.barcodesToSave[this.barcodeToAssign] = productId;
            jQuery('#barcode-div-' + productId).html(this.barcodeToAssign);

            this.barcodeToAssign = '';

            this.checkBarcode();
        },

        saveNewBarcodes: function()
        {
            var newBarcodesString = '';

            for (var elt in this.barcodesToSave) {
                if (this.barcodesToSave.hasOwnProperty(elt))
                    newBarcodesString += elt + '=' + this.barcodesToSave[elt] + ';';
            }

            jQuery('#new_barcodes').val(newBarcodesString);
        },

        getProductIdFromBarcode: function(barcode)
        {
            //exact matching
            if (this.barcodes[barcode])
                return this.barcodes[barcode];

            //matching with additional "0"
            if (this.barcodes["0"+barcode])
                return this.barcodes["0"+barcode];

            //matching removing leading "0"
            if (barcode.charAt(0) == "0")
            {
                if (this.barcodes[barcode.substr(1)])
                    return this.barcodes[barcode.substr(1)];
            }

        },

        showMessage: function (text, error) {
            if (text == '')
                text = '&nbsp;';

            if (error)
                text = '<font color="red">' + text + '</font>';
            else
                text = '<font color="green">' + text + '</font>';

            jQuery('#div_message').html(text);
            jQuery('#div_message').show();

            if (error)
                this.playNok();

        },

        playOk: function()
        {
            jQuery("#audio_ok").get(0).play();
        },

        playNok: function ()
        {
            jQuery("#audio_nok").get(0).play();
        },

        isAllowed: function (productId) {
            var currentProductQty =  parseInt(jQuery('#qty_' + productId).val());
            var received = parseInt(jQuery('#received_' + productId).val());
            var allowQty = received + currentProductQty;
            if (allowQty<0){
                alert('Qty should not be lower than Qty Received');
                jQuery('#qty_' + productId).val(received*-1);
            }

        },

        saveReceiveProducts:function () {
            if(this.negativeQty){
                //check if stock to decrement through negative reception is available
                if(!this.checkReceivedProductsStock())
                    return false;

                var verify = confirm("Some products have negative quantities, do you confirm ?");
                if(verify){
                    this.save();
                }else{
                    jQuery('#save').prop('disabled', false);
                    return false;
                }
            }else{
                this.save();
            }
        },

        checkReceivedProductsStock:function () {
            for(i=0;i<this.productIds.length; i++)
            {
                var productId = this.productIds[i];
                var productName = jQuery('#name_' + productId).val();
                var productStock = parseInt(jQuery('#qty_available_' + productId).val());
                var qtyToReceive = parseInt(jQuery('#qty_' + productId).val());
                if(qtyToReceive < 0 && Math.abs(qtyToReceive) > productStock)
                {
                    alert('You can not create a negative reception of '+ qtyToReceive +' qty for product '+ productName +' as its current stock is '+ productStock +'');
                    return false;
                }
            }

            return true;
        },

        save:function () {
            this.saveNewBarcodes();
            jQuery('#save').prop('disabled', true);
            jQuery('#edit_form').submit();
        }



    };

});
