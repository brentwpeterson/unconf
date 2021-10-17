( function ($, elementor) {
	"use strict";


    var Exhibs = {

        init: function () {
            
            var widgets = {
				'exhibz-speaker.default': Exhibs.Speaker_Image_Popup,
				'exhibz-speaker-slider.default': Exhibs.Speaker_Slider_popup,
				'exhibz-testimonial.default': Exhibs.Testimonial_Slider,
				'exhibz-countdown.default': Exhibs.Ts_Count_Down,
				'exhibz-slider.default': Exhibs.Main_Slider,
				'exhibz-gallery-slider.default': Exhibs.Exhibz_Gallery_Slider,
				'exhibz-event-category-slider.default': Exhibs.Exhibz_Category_Slider,
            };
            $.each(widgets, function (widget, callback) {
                elementor.hooks.addAction('frontend/element_ready/' + widget, callback);
            });
           
		},

		Exhibz_Category_Slider: function ($scope) {
			var $containers = $scope.find('.ts-event-category-slider');
		     
			if ($containers.length > 0) {
				var count =	$(".ts-event-category-slider").data("count");
				$containers.owlCarousel({
				  items: 6,
				  mouseDrag: true,
				  loop: false,
				  touchDrag: true,
				  autoplay:true,
				  nav: true,
				  dots: true,
				  autoplayTimeout: 5000,
				  autoplayHoverPause: true,
				  smartSpeed: 600,
				  navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
				  margin:20,
				  responsive:{
					  	375: {
							items: 1,
							nav: false,
						},
						600:{
							 items:3,
							 mouseDrag: false,
							 loop: false,
							 touchDrag: false,
						},
						1000:{
							 items:count
						}
				  }


			});
		  }	
        },

		Speaker_Slider_popup: function ($scope){
			var $container = $scope.find('.ts-image-popup');
			$container.magnificPopup({
				type: 'inline',
				closeOnContentClick: false,
				midClick: true,
				callbacks: {
				beforeOpen: function () {
					this.st.mainClass = this.st.el.attr('data-effect');
				}
				},
				zoom: {
				enabled: true,
				duration: 500, // don't foget to change the duration also in CSS
				},
				mainClass: 'mfp-fade',
			});

			var $containers = $scope.find('.ts-speaker-slider');
		     
			if ($containers.length > 0) {
				var count =	$(".ts-speaker-slider").data("count");
				$containers.owlCarousel({
				  items: 1,
				  mouseDrag: true,
				  loop: false,
				  touchDrag: true,
				  autoplay:true,
				  nav: false,
				  dots: true,
				  autoplayTimeout: 5000,
				  autoplayHoverPause: true,
				  smartSpeed: 600,
				  navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
				  margin:30,
				  responsive:{
						600:{
							 items:3,
							 mouseDrag: false,
							 loop: false,
							 touchDrag: false,
						},
						1000:{
							 items:count
						}
				  }


			});
		  }	

		},

		Testimonial_Slider: function ($scope) {
			var $testimonial_slider = $scope.find('.testimonial-carousel');
			var $quote_slider_count = $scope.find('.testimonial-carousel').data('count');
			
			$testimonial_slider.owlCarousel({
				items: $quote_slider_count,
				loop: true,
				smartSpeed: 900,
				dots: false,
				margin: 30,
				nav: $testimonial_slider.data("nav"),
				autoplay: $testimonial_slider.data("autoplay"),
				navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
				responsive: {
				   0: {
					  items: 1,
				   },
				   992: {
						items: $quote_slider_count,
					}
				}
			 });
        },

		Speaker_Image_Popup: function ($scope) {
			var $container = $scope.find('.ts-image-popup');
		
			$container.magnificPopup({
				type: 'inline',
				closeOnContentClick: false,
				midClick: true,
				callbacks: {
				beforeOpen: function () {
					this.st.mainClass = this.st.el.attr('data-effect');
				}
				},
				zoom: {
				enabled: true,
				duration: 500, // don't foget to change the duration also in CSS
				},
				mainClass: 'mfp-fade',
			});																										
		
		},
		//2nd start
		Ts_Count_Down: function ($scope) {
			var $container = $scope.find('.countdown');
		   var date_time = $container.data("event_time");    
			if ($container.length > 0) {
				$container.jCounter({
				   date: date_time,
				   serverDateSource:'',
				 
				   fallback: function () {
					  console.log("count finished!")
				   }
				});
			 }																									
		
		},
			//3rd start
		Main_Slider: function ($scope) {
			var $container = $scope.find('.main-slider');
			var controls= JSON.parse($container.attr('data-controls'));
			var autoslide = Boolean(controls.auto_nav_slide?true:false);
			var dot_nav_show = Boolean(controls.dot_nav_show?true:false);
		     
			    if ($container.length > 0) {
     
			       $container.owlCarousel({
						items: 1,
						mouseDrag: true,
						loop: true,
						touchDrag: true,
						autoplay:autoslide,
						dots: dot_nav_show,
						autoplayTimeout: 5000,
						animateOut: 'fadeOut',
						autoplayHoverPause: true,
						smartSpeed: 250,

			      });
			   }																									
		
		},
			//3rd start
			Exhibz_Gallery_Slider: function ($scope) {
			var $container = $scope.find('.ts-gallery-slider');
		     
			    if ($container.length > 0) {
     
			       $container.owlCarousel({
						items: 1,
						mouseDrag: true,
						loop: true,
						touchDrag: true,
						autoplay:true,
						nav: true,
						dots: false,
						autoplayTimeout: 5000,
						autoplayHoverPause: true,
						smartSpeed: 600,
						navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
						center: true,
						margin:0,
						responsive:{
							 600:{
								  items:3
							 },
							 1000:{
								  items:4
							 }
						}


			      });
			   }																									
		
		},

    };
    $(window).on('elementor/frontend/init', Exhibs.init);
}(jQuery, window.elementorFrontend) ); 