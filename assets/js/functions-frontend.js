jQuery(function ($) {
    "use strict";

    $('.ewp-dropdown-widget').on('change', function () {
        var href = $(this).find(":selected").val();
        location.href = href;
    });

    if (typeof $.fn.slick === 'function') {

        $('.ewp-carousel').slick({
            slide: '.ewp-slick-slide',
            infinite: true,
            draggable: false,
            prevArrow: '<div class="slick-prev"><span>' + ewp_ajax_object.carousel_prev + '</span></div>',
            nextArrow: '<div class="slick-next"><span>' + ewp_ajax_object.carousel_next + '</span></div>',
            speed: 300,
            lazyLoad: 'progressive',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 3,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2,
                        draggable: true,
                        arrows: false
                    }
                }
            ]
        });

        $('.ewp-product-carousel').slick({
            slide: '.ewp-slick-slide',
            infinite: true,
            draggable: false,
            prevArrow: '<div class="slick-prev"><span>' + ewp_ajax_object.carousel_prev + '</span></div>',
            nextArrow: '<div class="slick-next"><span>' + ewp_ajax_object.carousel_next + '</span></div>',
            speed: 300,
            lazyLoad: 'progressive',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        draggable: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        draggable: true,
                        arrows: false
                    }
                }
            ]
        });

    }

    /* ··························· Filter by publisher widget ··························· */

    var EWPFilterByPublisher = function () {

        var baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
        var currentUrl = window.location.href;

        var marcas = [];
        $('.ewp-filter-products input[type="checkbox"]').each(function (index) {
            if ($(this).prop('checked')) marcas.push($(this).val());
        });
        marcas = marcas.join();

        if (marcas) {

            //removes previous "ewp-publisher" from url
            currentUrl = currentUrl.replace(/&?ewp-publisher-filter=([^&]$|[^&]*)/i, "");

            //removes pagination
            currentUrl = currentUrl.replace(/\/page\/\d*\//i, "");

            if (currentUrl.indexOf("?") === -1) {
                currentUrl = currentUrl + '?ewp-publisher-filter=' + marcas;
            } else {
                currentUrl = currentUrl + '&ewp-publisher-filter=' + marcas;
            }

        } else {
            currentUrl = baseUrl;
        }

        location.href = currentUrl;

    }

    $('.ewp-filter-products button').on('click', function () { EWPFilterByPublisher(); });
    $('.ewp-filter-products.ewp-hide-submit-btn input').on('change', function () { EWPFilterByPublisher(); });

    var publishers = EWPgetUrlParameter('ewp-publisher-filter');

    if (publishers != null) {
        var publishers_array = publishers.split(',');
        $('.ewp-filter-products input[type="checkbox"]').prop('checked', false);
        for (var i = 0, l = publishers_array.length; i < l; i++) {
            $('.ewp-filter-products input[type="checkbox"]').each(function (index) {
                if ($(this).val()) {
                    if (publishers_array[i] == $(this).val()) {
                        $(this).prop('checked', true);
                    }
                }
            });
        }
    } else {
        $('.ewp-filter-products input[type="checkbox"]').prop('checked', false);
    }

    /* ··························· /Filter by publisher widget ··························· */

});

var EWPgetUrlParameter = function EWPgetUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
