define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate',
    'mage/dropdown'
], function (Component, customerData, $, ko, _) {
    'use strict';

    var mixin = {
        isEmailQuoteActive: function () {
            if(window.enableEmailQuoteConfig=="1")
            { 	
                $(".emailquotebutton").text(window.enableEmailQuotetitle);
            	return true;
            }else{
                $(".emailquotebutton").text("Send Quote");
            	return false;
            }
        },
        isButtonEnable: function () {
            if(window.buttonConfig =="0"){
             	return true;
            }else{
               if(window.enableEmailQuoteConfig=="1"){
            		return false;
            	}else{
            		return true;
            	}
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
