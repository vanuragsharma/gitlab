define([
    "jquery",
    "jquery/ui",
    ], function ($) {
        'use strict';
        $.widget('showprice.list', {
            options: {},
            _create: function () {
                var self = this;
                $(document).ready(function () {
                    var showPriceInfo = self.options.showPriceInfo;
                    var payHtml = self.options.payHtml;
                    var showPriceLink = self.options.showPriceLink;
                    var count = 0;
                    var isShowPrice = 0;
                    var showPriceLabel = self.options.showPriceLabel;
                    $(".products ol.product-items > li.product-item").each(function () {
                        var productLink = $(this).find(".product-item-link").attr("href");
                        if (showPriceInfo[productLink]['ShowPrice'] == 1) {
                            setShowPriceLabel($(this));
                        }
                    });
                    $(".products-grid.wishlist ol.product-items > li.product-item").each(function () {
                        var productLink = $(this).find(".product-item-link").attr("href");
                        if (showPriceInfo[productLink]['ShowPrice'] == 1) {
                            setShowPriceLabel($(this),productLink);
                        }
                    });
                    $("#product-comparison > tbody").each(function () {
                        var productLink = $(this).find(".product-item-name > a").attr("href");
                        if (showPriceInfo[productLink]['ShowPrice'] == 1) {
                            setShowPriceLabel($(this));
                        }
                    });
                    $('.action.tocart').click(function () {
                        var url = $(this).parents(".product-item-info").find(".product-item-link").attr("href");
                        isShowPrice = showPriceInfo[url]['ShowPrice'];
                        count = 0;
                    });
                    $('.action.tocart span').bind("DOMSubtreeModified",function () {
                        var title = $(this).text();
                        if (isShowPrice == 1) {
                            if (title == "Add to Cart") {
                                count++;
                                if (count == 1) {
                                    $(this).parent().attr("title", showPriceLabel);
                                    $(this).text(showPriceLabel);
                                }
                            }
                        }
                    });
                    function setShowPriceLabel(currentObject,productLink)
                    {
                        currentObject.find(".action.tocart.primary").attr("title",showPriceLabel);
                        currentObject.find(".action.tocart.primary").find("span").text(showPriceLabel[productLink].Label);
                    }
                });
            }
        });
        return $.showprice.list;
    });
    
    