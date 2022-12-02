/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    paths: {
        'owlcarousel': "Mageants_EventManager/js/owl.carousel",
        'fullcalendar': "Mageants_EventManager/js/fullcalendar.min",
		'moment': "Mageants_EventManager/js/moment.min",
        'fancybox': "Mageants_EventManager/js/jquery.fancybox"                
        
    },
    shim: {
        'owlcarousel': {
            deps: ['jquery']
        },
        'fullcalendar': {
            deps: ['jquery']
        },
        'moment': {
            deps: ['jquery']
        },
        'fancybox': {
            deps: ['jquery']
        }
        
    }
};