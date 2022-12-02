
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.AdminOrder = new Class.create();

    AdminOrder.prototype = {

        initialize: function (saveFieldUrl)
        {
            this.saveFieldUrl = saveFieldUrl;
        },

        addProduct: function (productId, buyingPrice, sku, name, packQtyOption, packQty)
        {
            var selectedQty = $('qty_' + productId).value;
            var tableBody = $('selected_products_table').tBodies[0];
            var productAlreadyAdded = {
                added:false,
                rowId:""
            };
            var row;
            sku = Base64.decode(sku);
            name = Base64.decode(name);

            for(row=1; row< tableBody.rows.length; row++){
                //Id format is selected_product_qty_ID
                var idArray = tableBody.rows[row].children[0].firstElementChild.id.split('_');
                var addedProductId = idArray[3];
                if(parseInt(productId) ===  parseInt(addedProductId)) {
                    productAlreadyAdded = {
                        added: true,
                        rowId: row
                    };
                }
            }

            if(productAlreadyAdded['added'] === true){
                this.updateTableRow(tableBody, productId, productAlreadyAdded['rowId'], selectedQty);
            }else{
                this.addTableRow(tableBody, productId, selectedQty, buyingPrice, sku, name, packQtyOption, packQty);
            }
        },

        addTableRow: function (tableBody, productId, selectedQty, buyingPrice, sku, name, packQtyOption, packQty)
        {
            var rowToAdd = tableBody.rows[0].cloneNode(true);

            //Add row
            rowToAdd.style.display='';

            //Qty
            rowToAdd.cells[0].children[0].value = selectedQty;
            rowToAdd.cells[0].children[0].id = rowToAdd.cells[0].children[0].id + '_' + productId;
            rowToAdd.cells[0].children[0].name = 'selected_product' + '[' + productId + '][qty]';

            //Pack qty text
            if(packQtyOption === 1)
            {
                if(packQty > 1){
                    rowToAdd.cells[0].children[1].innerHTML += '<b>x ' + packQty + '</b>';
                }

                //Pack qty hidden text box
                rowToAdd.cells[0].children[2].value = packQty;
                rowToAdd.cells[0].children[2].name = 'selected_product' + '[' + productId + '][pack_qty]';
            }
            //Pack qty hidden text box
            rowToAdd.cells[0].children[2].id = rowToAdd.cells[0].children[2].id + '_' + productId;

            //Buying price
            if(buyingPrice !== undefined){
                rowToAdd.cells[1].children[0].value = buyingPrice;
            }
            rowToAdd.cells[1].children[0].id = rowToAdd.cells[1].children[0].id + '_' + productId;
            rowToAdd.cells[1].children[0].name = 'selected_product' + '[' + productId + '][buying_price]';

            //SKU
            rowToAdd.cells[2].children[0].textContent = sku ;
            rowToAdd.cells[2].children[0].id = rowToAdd.cells[2].children[0].id + '_' + productId;

            //Name
            rowToAdd.cells[2].children[1].textContent = name;
            rowToAdd.cells[2].children[1].id = rowToAdd.cells[2].children[1].id + '_' + productId;

            tableBody.insertBefore(rowToAdd, tableBody.rows[0].nextSibling);
        },

        updateTableRow: function (tableBody, productId, rowId, selectedQty)
        {
            var rowToUpdate = tableBody.rows[rowId];
            var totalQty = parseInt(rowToUpdate.cells[0].children[0].value) + parseInt(selectedQty);

            rowToUpdate.cells[0].children[0].value = totalQty;
        },

        saveField: function(poId, popId, field, value)
        {
            var data = {
                FORM_KEY: window.FORM_KEY,
                po_id: poId,
                pop_id: popId,
                field: field,
                value: value
            };

            jQuery.ajax({
                url: this.saveFieldUrl,
                data: data,
                success: function (resp) {
                    //nothing
                },
                failure: function (resp) {
                    alert('An error occured during save');
                }
            });
        },

    };

});
