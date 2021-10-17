jQuery(document).ready(function ($) {
    'use strict';
   
    var container = jQuery('.etn-countdown-wrap');
    if (container.length > 0) {
        $.each(container, function (key, item) {

            // countdown
            let etn_event_start_date = '';
            etn_event_start_date = jQuery(item).data('start-date');

            var countDownDate = new Date(etn_event_start_date).getTime();

            let etn_timer_x = setInterval(function () {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                jQuery(item).find('.day-count').html(days);
                jQuery(item).find('.hr-count').html(hours);
                jQuery(item).find('.min-count').html(minutes);
                jQuery(item).find('.sec-count').html(seconds);
                if (distance < 0) {
                    clearInterval(etn_timer_x);
                    jQuery(this).find('.etn-countdown-wrap').html('EXPIRED');
                }
            }, 1000);
        });

    }


    //cart attendee 

    $(".etn-extra-attendee-form").on('blur change click', function () {
        $('.wc-proceed-to-checkout').css({
            'cursor': "default",
            'pointer-events': 'none'
        });
        $.ajax({
            url: etn_localize_event.rest_root + 'etn-events/v1/cart/attendee',
            type: 'GET',
            data: $('.woocommerce-cart-form').serialize(),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', etn_localize_event.nonce);
            },
            success: function (data) {
                $('.wc-proceed-to-checkout').css({
                    'cursor': "default",
                    'pointer-events': 'auto'
                });
            },

        });
    });

    // calculate total price of an event ticket
    $('.etn-event-form-qty').on('change keyup', function () {

        var __this = $(this);
        var form_price_amount_holder = __this.parents(".etn-event-form-parent").find('.etn_form_price');
        var add_to_cart_button = __this.parents(".etn-event-form-parent").find('.etn-add-to-cart-block');
        var product_left_qty = parseInt(__this.data("left_ticket"));
        var product_qty = parseInt(__this.val());
        var invalid_qty_message = __this.data("invalid_qty_text");
        
        if (product_qty <= product_left_qty && product_qty > 0) {
            var total_price = 0.00;
            var total_product_price = 0.00;
            var product_qty = parseInt(__this.parents(".etn-event-form-parent").find('.etn_product_qty').val());
            var product_price = parseFloat(__this.parents(".etn-event-form-parent").find('.etn_product_price').val());
            
            total_product_price = product_price;
            total_price = total_product_price * product_qty;
            form_price_amount_holder.html(total_price);
            if (add_to_cart_button.is(":hidden")) {
                add_to_cart_button.show();
            }
        } else {
            form_price_amount_holder.html(invalid_qty_message);
            add_to_cart_button.hide();
        }
    });
    $('.etn-event-form-qty').trigger('change');

    $('.schedule-tab').on('click', openScheduleTab);

    function openScheduleTab() {
        var title = $(this).data('title');
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(title).style.display = "block";
    }

    $('.schedule-tab-shortcode').on('click', openScheduleTabShortCode);

    function openScheduleTabShortCode() {
        var title = $(this).data('title');
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent-shortcode");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks-shortcode");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        let single_title = "shortcode_" + title;
        document.getElementById(single_title).style.display = "block";
    }

    $('.attr-nav-pills>li>a').first().trigger('click');


    //   custom tabs
    // $(document).on('click', '.etn-tab-a', function (event) {
    //     event.preventDefault();

    //     $(this).parents(".schedule-tab-wrapper").find(".etn-tab").removeClass('tab-active');
    //     $(this).parents(".schedule-tab-wrapper").find(".etn-tab[data-id='" + $(this).attr('data-id') + "']").addClass("tab-active");
    //     $(this).parents(".schedule-tab-wrapper").find(".etn-tab-a").removeClass('etn-active');
    //     $(this).parent().find(".etn-tab-a").addClass('etn-active');
    // });

       //   custom tabs
    $(document).on('click', '.etn-tab-a', function (event) {
        event.preventDefault();

        $(this).parents(".etn-tab-wrapper").find(".etn-tab").removeClass('tab-active');
        $(this).parents(".etn-tab-wrapper").find(".etn-tab[data-id='" + $(this).attr('data-id') + "']").addClass("tab-active");
        $(this).parents(".etn-tab-wrapper").find(".etn-tab-a").removeClass('etn-active');
        $(this).parent().find(".etn-tab-a").addClass('etn-active');
    });

    //======================== Attendee form validation start ================================= //

    /**
     * Get form value and send for validation
     */
    $(".attendee_sumbit").prop('disabled', true).addClass('attendee_sumbit_disable');

    function button_disable(button_class) {
        var length = $(".attendee_error").length;
        var attendee_submit = $( button_class );

        if (length == 0) {
            attendee_submit.prop('disabled', false).removeClass('attendee_sumbit_disable');
        } else {
            attendee_submit.prop('disabled', true).addClass('attendee_sumbit_disable');
        }
    }
    // if update form exist check validation
    
    if ( $(".attendee_update_sumbit").length > 0 ) {
        var attendee_update_field = [
            "input[name='name']", 
        ]; 

        if ($(".etn-attendee-extra-fields").length > 0 ) {
            var form_data = []; var attendee_update_field = [];
            $("input:not(:submit,:hidden)").each(function() {
                form_data.push({name: this.name, value: this.value });
            });
            if ( form_data.length > 0 ) {
                form_data.map(function (obj) {
                    if( $("input[name='"+obj.name + "']").attr('type') !=="hidden" ){
                        attendee_update_field.push( "input[name='"+obj.name + "']" )
                    }
                });
            }
        }
        
        validation_checking( attendee_update_field , ".attendee_update_sumbit");
    }

    if ( $(".attendee_sumbit").length > 0 ) {

        var attendee_field = [
            "input[name='attendee_name[]']", 
        ];

        if ($(".etn-attendee-extra-fields").length > 0 ) {
            var form_data = []; var attendee_field = [];
            $("input:not(:submit,:hidden)").each(function() {
                form_data.push({name: this.name, value: this.value });
            });
            if ( form_data.length > 0 ) {
                form_data.map(function (obj) {
                    if( $("input[name='"+obj.name + "']").attr('type') !=="hidden" ){
                        attendee_field.push( "input[name='"+obj.name + "']" )
                    }
                });
            }
        }
        
        validation_checking( attendee_field , ".attendee_sumbit");
    }

    function validation_checking(input_arr , button_class ) {
        var in_valid = [];
        $.each(input_arr, function (index, value) {
            
            // check if value already exist in input
            switch ( $(value).attr('type') ) {
                case "text":
                    if ( typeof $(this).val() ==="undefined" ||  $(this).val() =="" ) {
                        $(this).addClass("attendee_error");
                        in_valid.push(value);
                    }
                    break;

                case "radio":
                    if ( typeof $(value+":checked").val() ==="undefined" ) {
                        $(this).addClass("attendee_error");
                        in_valid.push(value);
                    }
                    break;

                default:
                    break;
            }

            // if no value exist check input on key change
            $(".attende_form").on("keyup change", value, function () {
                var response = get_error_message($(this).attr('type'), $(this).val());
                var id = $(this).attr("id");
                $("." + id).html("");
                if (typeof response !== "undefined" && response.message !== 'success') {
                    $("." + id).html(response.message);
                    $(this).addClass("attendee_error");
                } else {
                    $(value).removeClass("attendee_error");
                }
                button_disable(button_class);

            });

        });

        // check if value already exist in input
        if (in_valid.length>0) {
            $(button_class).prop('disabled', true).addClass('attendee_sumbit_disable');
        } else {
            $(button_class).prop('disabled', false).removeClass('attendee_sumbit_disable');
        }
    }


    /**
     * Check type and input validation
     * @param {*} type 
     * @param {*} value 
     */
    function get_error_message(type, value) {
        var response = {
            error_type: "no_error",
            message: "success"
        };
        if (value.length == 0) {
            $(this).addClass("attendee_error");
        } else {
            $(this).removeClass("attendee_error");
        }

        switch (type) {
            case 'email':
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (value.length !== 0) {
                    if (re.test(String(value).toLowerCase()) == false) {
                        response.error_type = "not-valid";
                        response.message = "Email is not valid";
                    }
                } else {
                    response.error_type = "empty";
                    response.message = "Please fill the field";
                }
                break;
            case 'tel':

                if (value.length === 0) {
                    response.error_type = "empty";
                    response.message = "Please fill the field";
                } else if (value.length > 15) {
                    response.error_type = "not-valid";
                    response.message = "Invalid phone number";
                } else if (!value.match(/^\d+/) == true) {
                    response.error_type = "not-valid";
                    response.message = "Only number allowed";
                }
                break;
            case 'text':
                if (value.length === 0) {
                    response.error_type = "empty";
                    response.message = "Please fill the field";
                }
                break;
            case 'radio':
                if ( value =="" ) {
                    response.error_type = "not-selected";
                    response.message = "Please check the field";
                }
                break;
            case 'number':
                if ( value.length === 0 ) {
                    response.error_type = "empty";
                    response.message = "Please input a number";
                }
                break;
            default:
                break;
        }

        return response;
    }

    //====================== Attendee form validation end ================================= //

    //===================================
    //  advanced ajax search
    //================================= //

    if ($('.etn_event_inline_form').length) {
        if ($(".etn-event-archive-wrap").length === 0) {
            $(".etn-event-wrapper").before('<div class="etn_event_ajax_preloader"><div class="lds-dual-ring"></div></div>');
        }

        function ajax_load(current, search_params) {
            let ajax_wraper = $(".etn-event-archive-wrap");
            // let data_params = ajax_wraper.attr("data-json");
            // let data_parse = JSON.parse(data_params);
            // let loading_btn = $('.etn_load_more_button');

            const queryString = new URL(window.location);
            queryString.searchParams.set(search_params, current.value);
            window.history.pushState({}, '', queryString);

            const queryValue = new URLSearchParams(window.location.search);

            let etn_categorys         = queryValue.get("etn_categorys"),
                etn_event_location    = queryValue.get("etn_event_location"),
                etn_event_date_range  = queryValue.get("etn_event_date_range"),
                etn_event_will_happen = queryValue.get("etn_event_will_happen"),
                keyword = queryValue.get("s");

            if ((keyword !== null && keyword.length) || (etn_event_location !== null && etn_event_location.length) || (etn_categorys !== null && etn_categorys.length) || (etn_event_date_range !== null && etn_event_date_range.length) || (etn_event_will_happen !== null && etn_event_will_happen.length)) {
                ajax_wraper.parents('.etn_search_item_container').find('.etn_event_ajax_preloader').addClass('loading');
                let data = {
                    'action': 'etn_event_ajax_get_data',
                    etn_categorys,
                    etn_event_location,
                    etn_event_date_range,
                    etn_event_will_happen,
                    's': keyword,
                };
                let i = 0;
                jQuery.ajax({
                    url: form_data.ajax_url,
                    data,
                    method: 'POST',
                    beforeSend: function() {
                        ajax_wraper.parents('.etn_search_item_container').find('.etn_event_ajax_preloader').addClass('loading');
                        i++;
                    },
                    success: function (content) {
                        ajax_wraper.parents('.etn_search_item_container').find('.etn_event_ajax_preloader').removeClass('loading');
                        $('.etn_search_item_container').find('.etn-event-wrapper').html(content);
                    },
                    complete: function() {
                        i--;
                        if (i <= 0) {
                            ajax_wraper.parents('.etn_search_item_container').find('.etn_event_ajax_preloader').removeClass('loading');
                        }
                    },
                })
            }
        }
        if ($('[name="etn_event_location"]').length) {
            $('[name="etn_event_location"]').on("change", function (e) {
                ajax_load(this, 'etn_event_location');
            });
        }
        
        if ($('[name="etn_categorys"]').length) {
            $('[name="etn_categorys"]').on("change", function (e) {
                ajax_load(this, 'etn_categorys');
            });
        }
        if ($('.etn_event_inline_form').find('[name="s"]').length) {
            $('.etn_event_inline_form').find('[name="s"]').on("keyup", function (e) {
                ajax_load(this, 's');
            })
        }
        if ($('[name="etn_event_date_range"]').length) {
            $('[name="etn_event_date_range"]').on("change", function (e) {
                ajax_load(this, 'etn_event_date_range');
            })
        }
        if ($('[name="etn_event_will_happen"]').length) {
            $('[name="etn_event_will_happen"]').on("change", function (e) {
                ajax_load(this, 'etn_event_will_happen');
            })
        }
     
    }
   //===================================
   //  meta tag added in attendee registration page
    //================================= //
    
    $('.etn-attendee-registration-page').before('<meta name="viewport" content="width=device-width, initial-scale=1.0"/>');

     /*================================
     Event accordion
    ===================================*/
    
    $('.etn-recurring-widget .etn-recurring-header').click(function() {

        $(".etn-recurring-widget").removeClass("active").addClass("no-active").find(".etn-zoom-event-notice").slideUp();
        if ($(this).parents(".recurring-content").hasClass("active")) {
            $(this).parents(".recurring-content").removeClass("active").find(".etn-form-wrap").slideUp();

        } else {
            $(".etn-recurring-widget .recurring-content.active .etn-form-wrap").slideUp();
            $(".etn-recurring-widget .recurring-content.active").removeClass("active");
            $(this).parents(".recurring-content").addClass("active").find(".etn-form-wrap").slideDown();
            $(this).parents(".etn-recurring-widget").addClass("active").removeClass("no-active").find(".etn-zoom-event-notice").slideDown();
        }
        
    });


  $(document).mouseup(function(e) {
        var container = $(".etn-recurring-widget");
        if (!container.is(e.target) && container.has(e.target).length === 0) 
        {
            container.removeClass("no-active");
        }
    });
    
    // recurring event loadmore
    $(document).ready(function(){
        var count = $(".etn-recurring-widget").length;
        var limit = 3;
        $(".etn-recurring-widget").slice(0, limit).show();
        if(count <= limit){
            $("#seeMore").fadeOut();;
        }
		$("body").on('click touchstart', '#seeMore', function (e) {
			e.preventDefault();
			$(".etn-recurring-widget:hidden").slice(0, limit).slideDown();
			if ($(".etn-recurring-widget:hidden").length == 0) {
				$("#seeMore").fadeOut();
			}
		});
    })

});


