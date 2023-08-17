$(function() {

    "use strict";


    /*-----------------------------------
     * SIDEBAR & MENU
     *-----------------------------------*/
    var scrollable = $('#fscontent');
    $(window).on('load', function() {
        scrollable.find('.aos-init').removeClass('aos-animate');
    });
    var hamburger = $('.hamburger');
    hamburger.on('click', function() {
        $('body').toggleClass('sidebar-open');
        // start animating elements in vieport once content in open
        setTimeout(function() {
            scrollable.find('.aos-init:not(.aos-animate):in-viewport').addClass('aos-animate');
        }, 1200);

    });

    scrollable.on('scroll', function() {
        scrollable.find('.aos-init:not(.aos-animate):in-viewport').addClass('aos-animate');
    });

    /*-----------------------------------
     * FORM STYLING
     *-----------------------------------*/


    $('input, textarea').keyup(function() {
        if ($(this).val() == '') {
            $(this).siblings('label').removeClass('active');
        } else {
            $(this).siblings('label').addClass('active');
        }
    });

    /*-----------------------------------
     * Slick Slider
     *-----------------------------------*/

    if ($('#bg-slider').length && $.fn.slick) {

        $('#bg-slider').slick({
            arrows: false,
            autoplay: true,
            fade: true,
            dots: true,
            appendDots: $('.wrapper'),
            speed: 1000,
            autoplaySpeed: 5000
        });


        $(window).on('load', function() {
            $('.slick-slide .img-holder').height($(window).height());
        });

        $(window).on('resize', function() {
            $('.slick-slide .img-holder').height($(window).height());
        });

    }

    /*-----------------------------------
     * COUNTDOWN JS
     *-----------------------------------*/

    var $countdownDiv = $('#countdown');
    if ($countdownDiv.length && $.fn.countdown) {

        /*Fetch Event Date From HTML via data-* attr */
        var get_date = $countdownDiv.data('event-date');
        /*init*/
        if (get_date) {

            $countdownDiv.countdown({
                date: get_date,
                /*Change date and time in HTML data-event-date attribute */
                format: "on"
            });
        }
    }


});
/* ----------------------------End Fn*/

/*-----------------------------------
 * ANIMATE ON SCROLL - AOS
 *-----------------------------------*/

$(window).on('load', function() {
    AOS.init({
        duration: 1200,
    })
});