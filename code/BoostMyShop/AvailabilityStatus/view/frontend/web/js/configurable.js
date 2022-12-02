
define([
    'jquery',
    "prototype",
    "configurable"
], function (jQuery) {
    'use strict';

    window.AvailabilityConfigurable = new Class.create();

    AvailabilityConfigurable.prototype = {

        initialize: function () {

        },

        init: function (availabilities) {
            this.availabilities = availabilities;
            this.simpleProductHidden = jQuery('[name="selected_configurable_option"]');

            jQuery( ".price-box" ).on( "updatePrice", function() {
                var productId = objAvailabilityConfigurable.simpleProductHidden.val();

                if(!productId)
                {
                    var selected_options = {};
                    jQuery('div.swatch-attribute').each(function(k,v){
                        var attribute_id    = jQuery(v).attr('data-attribute-id');
                        var option_selected = jQuery(v).attr('data-option-selected');

                        if(!attribute_id || !option_selected){return;}
                        selected_options[attribute_id] = option_selected;
                    });

                    if(jQuery('[data-role=swatch-options]').data('mageSwatchRenderer'))
                    {
                        var product_id_index = jQuery('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
                        var found_ids = [];
                        jQuery.each(product_id_index, function(product_id,attributes){
                            var productIsSelected = function(attributes, selected_options){
                                return _.isEqual(attributes, selected_options);
                            }
                            if(productIsSelected(attributes, selected_options)){
                                found_ids.push(product_id);
                                productId = found_ids[0];
                            }
                        });
                    }
                }

                if (productId)
                    jQuery('#availability-configurable')[0].innerHTML = objAvailabilityConfigurable.availabilities[productId].message;
            });

        }

    }

});
