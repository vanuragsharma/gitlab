
define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.stockTransferReception = new Class.create();

    stockTransferReception.prototype = {

        initialize: function(){

        },

        init : function(productIds, barcodes){
            this.productIds = productIds;
            this.barcodes = barcodes;

            this.KC_value = '';
            jQuery(document).on('keypress', {obj: this}, this.handleKey);

            this.showMessage('Barcode scan enabled');
        },

        qtyChanged: function(){
            this.updateRowColors();
            this.updateSummary();
        },

        qtyMini: function(productId) {
            jQuery('#qty_' + productId).val(0);
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
            if (jQuery('#qty_' + productId).val() > 0)
                jQuery('#qty_' + productId).val(jQuery('#qty_' + productId).val() - 1);
            this.qtyChanged();
        },

        updateRowColors: function(){
            var i;
            for(i=0;i<this.productIds.length; i++)
            {
                var productId = this.productIds[i];
                var delta = jQuery('#remaining_' + productId).val() - jQuery('#qty_' + productId).val()

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
                this.showMessage('Unknown barcode ' + barcode, true);
                return;
            }
            else
            {
                jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);

                var productName = jQuery('#name_' + productId).val();
                var msg = '"' + productName + '" scanned<br>' + jQuery('#qty_' + productId).val() + ' / ' + jQuery('#remaining_' + productId).val();
                this.showMessage(msg);
                this.qtyChanged();
                this.playOk();
            }
        },

        getProductIdFromBarcode: function(barcode)
        {
            if (this.barcodes[barcode])
                return this.barcodes[barcode];
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
        }

    };

});
