jQuery( document ).ready( function($){
   "use strict";

      /**-------------------------------------------------
       *Fixed HEader
     *----------------------------------------------------**/
    $(window).on('scroll', function () {

      /**Fixed header**/
      if ($(window).scrollTop() > 250) {
         $('.navbar-fixed').addClass('sticky fade_down_effect');
      } else {
         $('.navbar-fixed').removeClass('sticky fade_down_effect');
      }
   });


/* ----------------------------------------------------------- */
   /*  Mobile Menu
   /* ----------------------------------------------------------- */
   if ($('.navbar').length > 0) {
      $('.menu-item-has-children').each(function () {
         $(this).append('<span class="dropdown-menu-toggle"></span>');
      })
      $('.dropdown-menu-toggle').on('click', function(e) {
         var dropdown = $(this).parent('.dropdown');
         dropdown.find('>.dropdown-menu').slideToggle('show');
         $(this).toggleClass('opened');
         return true;
      });
      $('.dropdown-toggle').on('click', function () {
         var location = $(this).attr('href');
         window.location.href = location;
         return true;
      });
   }

    

/* ----------------------------------------------------------- */
   /*  Site search
   /* ----------------------------------------------------------- */


   $('.nav-search').on('click', function () {
      $('.search-block').fadeIn(350);
      $('.nav-search').addClass('hide');
   });

   $('.search-close').on('click', function () {
      $('.search-block').fadeOut(350);
      $('.nav-search').removeClass('hide');
   });

   $(document).on('mouseup', function (e) {
      var container = $(".nav-search-area");

      if (!container.is(e.target) && container.has(e.target).length === 0) {
         $('.search-block').fadeOut(350);
         $('.nav-search').removeClass('hide');
      }

  }); 
  
   /* ----------------------------------------------------------- */
   /*  Back to top
   /* ----------------------------------------------------------- */

   $(window).on('scroll', function () {
    if ($(window).scrollTop() > $(window).height()) {
       $(".BackTo").fadeIn('slow');
    } else {
       $(".BackTo").fadeOut('slow');
    }

    });

    $("body, html").on("click", ".BackTo", function (e) {
        e.preventDefault();
        $('html, body').animate({
          scrollTop: 0
        }, 800);
    });

      /*==========================================================
               go current section
      ======================================================================*/
      $('.scroll a').on('click', function() {
         $('html, body').animate({scrollTop: $(this.hash).offset().top - 70}, 1000);
         return false;
     });
     
     if ($('.box-style').length > 0) {
      $('.box-style').each(function () {
         if ($(this).find('.elementor-row').length > 0) {
            $(this).find('.elementor-row').append('<div class="indicator"></div>')
         }
      })
   }
     /*==========================================================
               video popup
      ======================================================================*/
   if ($('.video-btn').length > 0) {
      $('.video-btn').magnificPopup({
          type: 'iframe',
          mainClass: 'mfp-with-zoom',
          zoom: {
              enabled: true, // By default it's false, so don't forget to enable it
   
              duration: 300, // duration of the effect, in milliseconds
              easing: 'ease-in-out', // CSS transition easing function
   
              opener: function (openerElement) {
                  return openerElement.is('img') ? openerElement : openerElement.find('img');
              }
          }
      });
   }
     /*==========================================================
               video popup
      ======================================================================*/
   if ($('.gallery-popup').length > 0) {
      $('.gallery-popup').magnificPopup({
          type: 'inline',
          mainClass: 'mfp-with-zoom',
          zoom: {
              enabled: true, // By default it's false, so don't forget to enable it
   
              duration: 300, // duration of the effect, in milliseconds
              easing: 'ease-in-out', // CSS transition easing function
   
              opener: function (openerElement) {
                  return openerElement.is('img') ? openerElement : openerElement.find('img');
              }
          }
      });
   }

   /*==========================================================
                        Preloader
   ======================================================================*/
      $(window).on('load', function () {         
         setTimeout(() => {
            $('#preloader').addClass('loaded');
         }, 1000);
         
         });
         
      // preloader close
      $('.preloader-cancel-btn').on('click', function (e) {
         e.preventDefault();
         if (!($('#preloader').hasClass('loaded'))) {
            $('#preloader').addClass('loaded');
         }
      });

      /*=========================
      // instagram feed
      ============================ */

      if ($('#instafeed').length > 0) {
         var InstagramToken = $('#instafeed').data('token'),
            limit = $('#instafeed').data('media-count');
         var feed = new Instafeed({
             accessToken: InstagramToken,
             limit: Number(limit),
             template: '<a href="{{link}}" target="_blank"><img title="{{caption}}" src="{{image}}" /></a>',
             transform: function(item) {
             var d = new Date(item.timestamp);
             item.date = [d.getDate(), d.getMonth(), d.getYear()].join('/');
             return item;
             }
         });
         
         feed.run();
      }

   
      /*================================
      // countdown 
    ===================================*/
      var main_block = $(".count_down_block")
      if ( main_block.length > 0 ) {
         count_down($, main_block); 
      }
      
      // coundown function
      function count_down($, $scope) {
         var $container = $scope.find('.etn-event-countdown-wrap');
         var date_texts   = $container.data('date-texts');

         var day_text = date_texts.day;
         var hour_text = date_texts.hr;
         var min_text = date_texts.min;
         var second_text = date_texts.sec;
         var days_text = date_texts.days;
         var hours_text =  date_texts.hrs;
         var mins_text = date_texts.mins;
         var seconds_text = date_texts.secs;

         if ($container.length > 0) {
            var targetDate = $container.data('start-date');
            var targetNode = $scope.find(".etn-countdown-parent");
            $(targetNode).countdown({
               date: targetDate,
               day: day_text,
               days: days_text,
               hour: hour_text,
               hours: hours_text,
               minute: min_text,
               minutes: mins_text,
               second: second_text,
               seconds: seconds_text,
               hideOnComplete: true
            }, function (container) {
               $scope.html(exhibz_data.event_expire);
            });
         }
      }    
} );


