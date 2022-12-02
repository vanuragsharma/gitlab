var config = {
    deps: [
        'BoostMyShop_OrderPreparation/js/hold',
        "BoostMyShop_OrderPreparation/js/batchpopup",
        "BoostMyShop_OrderPreparation/js/manifest"
    ],
    map: {
        '*': {
            orderpreparation_packing:  'BoostMyShop_OrderPreparation/js/packing'
        }
    },
};