jQuery(function($) {
    $(document).ready(function () {
        $('.owl-carousel').owlCarousel({
            items: 4,
            autoplay: true,
            loop: true
        });

        $('#section2').waypoint(function () {
            $('#section2').addClass('animated fadeInUp');
        }, {
            offset: '75%'
        });

        $('#section3').waypoint(function () {
            $('#section3').addClass('animated fadeInDown');
        }, {
            offset: '75%'
        });

        $('#section4').waypoint(function () {
            $('#section4').addClass('animated fadeInRight');
        }, {
            offset: '75%'
        });

        $('#section5').waypoint(function () {
            $('#section5').addClass('animated fadeInLeft');
        }, {
            offset: '75%'
        });

        $('#section6').waypoint(function () {
            $('#section6').addClass('animated bounceInDown');
        }, {
            offset: '75%'
        });

        $('#section7').waypoint(function () {
            $('#section7').addClass('animated fadeInLeftBig');
        }, {
            offset: '75%'
        });

        $('#section8').waypoint(function () {
            $('#section8').addClass('animated bounceInTop');
        }, {
            offset: '75%'
        });

    });

    $(document).ready(function(){
        $(".toggle-text").click(function(){
            $(".menu").slideToggle(700);
        });
    });

    //$(document).ready(function(){
    //    if(window.location.hash) { //If page loads with hash
    //        scrollToID(window.location.hash);
    //    }
    //
    //    //$('section').scroll(function(){
    //    $(window).scroll(function () {
    //        console.log($(this).scrollTop());
    //        //console.log($(this).attr('id'));
    //        //console.log($(this).attr('id'));
    //    });
    //
    //    $('a[href^="#"]').click(function(event){ //Only target links that start with #
    //        event.preventDefault();
    //        scrollToID($(this).attr('href'));
    //    });
    //});
    //
    //function scrollToID(ID){
    //    $('#nav > li').removeClass('active');
    //    $('a[href="' + ID + '"]').parent().addClass('active');
    //    var navOffset = ($(window).width() > 979) ? 93 : 0; //adjust this if you have a fixed header with responsive design
    //    $('html, body').animate({
    //        'scrollTop': $(ID).offset().top - navOffset
    //    }, 'slow');
    //}

});