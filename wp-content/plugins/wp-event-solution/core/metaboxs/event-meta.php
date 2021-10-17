<?php

namespace Etn\Core\Metaboxs;

use Etn\Utils\Helper;

defined( 'ABSPATH' ) || exit;

class Event_meta extends Event_manager_metabox {

    public $report_box_id = '';
    public $event_fields  = [];
    public $cpt_id        = 'etn';
    public $banner_fields = [];
    public $text_domain   = 'eventin';

    public function register_meta_boxes() {
        
        $metabox_array = [
            'etn_event_settings'    => [
                'label'     => esc_html__( 'Eventin Event Settings', 'eventin' ),
                'callback'  => 'display_callback',
                'cpt_id'    => $this->cpt_id,
            ],
            'etn_report'    => [
                'label'     => esc_html__( 'Order Report', 'eventin' ),
                'callback'  => 'etn_report_callback',
                'cpt_id'    => $this->cpt_id,
            ],
        ];
        
        $is_child_post  = wp_get_post_parent_id( get_the_ID() ) ? true : false;
        $all_boxes      = apply_filters( 'etn/metaboxs/etn_metaboxs', $metabox_array );
        $all_boxes      = $this->filter_meta_boxes_for_parent_child( $all_boxes, $is_child_post );
        
        foreach( $all_boxes as $box_id => $metabox ){
            $instance       = !empty( $metabox["instance"] ) ? $metabox["instance"] : $this;
            add_meta_box( 
                $box_id, 
                esc_html__( $metabox['label'], 'eventin' ), 
                [ $instance, $metabox['callback'] ], 
                $metabox['cpt_id'] 
            );
        }

    }

    /**
     * Input fields array for event meta
     */
    public function etn_meta_fields() {
        $settings = \Etn\Core\Settings\Settings::instance()->get_settings_option();

        $default_fields = [
            'etn_event_location'        => [
                'label'    => esc_html__( 'Event location', 'eventin' ),
                'desc'     => esc_html__( 'Place event location', 'eventin' ),
                'type'     => 'text',
                'default'  => '',
                'value'    => '',
                'priority' => 1,
                'required' => true,
                'attr'     => ['class' => 'etn-label-item'],
            ],
            'etn_event_schedule'        => [
                'label'    => esc_html__( 'Event schedule', 'eventin' ),
                'desc'     => esc_html__( 'Select all schedules created for this event', 'eventin' ),
                'type'     => 'select2',
                'options'  => Helper::get_schedules(),
                'priority' => 1,
                'required' => true,
                'attr'     => ['class' => 'etn-label-item'],
            ],
            'etn_event_organizer'       => [
                'label'    => esc_html__( 'Organizers', 'eventin' ),
                'desc'     => esc_html__( 'Select speaker category which will be used as organizer', 'eventin' ),
                'type'     => 'select_single',
                'options'  => Helper::get_orgs(),
                'priority' => 1,
                'required' => true,
                'attr'     => ['class' => 'etn-label-item'],
            ]
        ];
        $default_fields['etn_start_time'] = [
                'label'    => esc_html__( 'Event start time', 'eventin' ),
                'desc'     => esc_html__( 'Select start time', 'eventin' ),
                'type'     => 'time',
                'default'  => '',
                'value'    => '',
                'priority' => 1,
                'required' => false,
                'attr'     => ['class' => 'etn-label-item'],
        ];
        $default_fields['etn_end_time'] = [
                'label'    => esc_html__( 'Event end time', 'eventin' ),
                'type'     => 'time',
                'default'  => '',
                'desc'     => esc_html__( 'Select end time', 'eventin' ),
                'value'    => '',
                'priority' => 1,
                'required' => false,
                'attr'     => ['class' => 'etn-label-item'],
        ];
        $default_fields['event_timezone'] = [
                'label'    => esc_html__( 'Timezone', 'eventin' ),
                'type'     => 'timezone',
                'default'  => '',
                'desc'     => esc_html__( 'Event will be held on this time-zone', 'eventin' ),
                'value'    => '',
                'priority' => 1,
                'required' => false,
                'attr'     => ['class' => 'etn-label-item'],
        ];
 
        $default_fields['etn_start_date'] = [
                'label'    => esc_html__( 'Event start date', 'eventin' ),
                'desc'     => esc_html__( 'Select event start date', 'eventin' ),
                'type'     => 'text',
                'default'  => '',
                'value'    => '',
                'priority' => 1,
                'required' => false,
                'attr'     => ['class' => 'etn-label-item etn-date'],
        ];
        $default_fields['etn_end_date'] = [
                'label'    => esc_html__( 'Event event end date', 'eventin' ),
                'type'     => 'text',
                'default'  => '',
                'value'    => '',
                'desc'     => esc_html__( 'Select end date', 'eventin' ),
                'priority' => 1,
                'required' => false,
                'attr'     => ['class' => 'etn-label-item etn-date'],
        ];
        if( !empty( $settings['sell_tickets'] ) && class_exists('WooCommerce') ){
            $default_fields['etn_registration_deadline'] = [
                    'label'    => esc_html__( 'Registration deadline', 'eventin' ),
                    'type'     => 'text',
                    'default'  => '',
                    'desc'     => esc_html__( 'Select event registration deadline', 'eventin' ),
                    'value'    => '',
                    'priority' => 1,
                    'required' => false,
                    'attr'     => ['class' => 'etn-label-item etn-date'],
            ];
        }

        $default_fields['recurring_enabled'] = [
            'label'        => esc_html__( 'Recurring event', 'eventin' ),
            'desc'         => esc_html__( 'Set this event as a recurring event', "eventin" ),
            'type'         => 'checkbox',
            'left_choice'  => 'yes',
            'right_choice' => 'no',
            'attr'         => ['class' => 'etn-label-item etn-enable-recurring-event'],
            'conditional'  => true,
            'condition-id' => 'etn_event_recurrence',
        ];
        $default_fields['etn_event_recurrence'] = [
            'label'        => esc_html__( 'Set recurrence', 'eventin' ),
            'desc'         => esc_html__( 'Set condition for recurrences. Must select event start date and event end date. Otherwise this feature won\'t work', "eventin" ),
            'type'         => 'recurrence_block',
            'attr'         => ['class' => 'etn-label-item set_recurrence_item'],
        ];

        if( !empty( $settings['sell_tickets'] ) && class_exists('WooCommerce') ){
            $default_fields['_virtual'] = [
                'label'        => esc_html__( 'Virtual Product', 'eventin' ),
                'desc'         => esc_html__( 'Set if you want to register this event as a Woocommerce Virtual Product. It will handle all behaviors related to Woocommerce Virtual Product', "eventin" ),
                'type'         => 'radio',
                'options'       => [
                    'yes'   =>'yes',
                    'no'   =>'no',
                ]
            ];
        }
        if( !empty( $settings['sell_tickets'] ) && class_exists('WooCommerce') && ( 'yes' == get_option( 'woocommerce_calc_taxes' ) )){
            $default_fields['_tax_status'] = [
                'label'        => esc_html__( 'Tax Status', 'eventin' ),
                'desc'         => esc_html__( 'Set if you want to enable Woocommerce Tax on this event. First you need to enable tax calculation from Woocommerce. After that, if you turn on tax status, then Standard Tax Rates will be applied on this event', "eventin" ),
                'type'         => 'radio',
                'options'       => [
                    'taxable'   =>'yes',
                    'none'      =>'no',
                ]
            ];
        }
        if ( !empty( $settings['etn_zoom_api'] ) ) {
            $default_fields['etn_zoom_event'] = [
                'label'        => esc_html__( 'Zoom Event', "eventin" ),
                'desc'         => esc_html__( 'Enable if this event is a zoom event', "eventin" ),
                'type'         => 'checkbox',
                'left_choice'  => 'Yes',
                'right_choice' => 'no',
                'attr'         => ['class' => 'etn-label-item etn-zoom-event'],
                'conditional'  => true,
                'condition-id' => 'etn_zoom_id',
            ];

            $default_fields['etn_zoom_id'] = [
                'label'    => esc_html__( 'Select Meeting', "eventin" ),
                'desc'     => esc_html__( 'Choose zoom meeting for this event', "eventin" ),
                'type'     => 'select_single',
                'options'  => Helper::get_zoom_meetings(),
                'priority' => 1,
                'required' => true,
                'attr'     => ['class' => 'etn-label-item etn-zoom-id'],
            ];
        }

        $default_fields['etn_ticket_availability'] = [
            'label'        => esc_html__( 'Limited Tickets', 'eventin' ),
            'desc'         => esc_html__( 'Set if you want to limit available tickets', "eventin" ),
            'type'         => 'checkbox',
            'left_choice'  => 'limited',
            'right_choice' => 'unlimited',
            'attr'         => ['class' => 'etn-label-item etn-limit-event-ticket'],
            'conditional'  => true,
            'condition-id' => 'etn_avaiilable_tickets',
        ];
        $default_fields['etn_avaiilable_tickets'] = [
            'label'    => esc_html__( 'No. of Tickets', 'eventin' ),
            'type'     => 'number',
            'default'  => '',
            'value'    => '',
            'desc'     => esc_html__( 'Total no of ticket', 'eventin' ),
            'priority' => 1,
            'required' => true,
            'attr'     => ['class' => 'etn-label-item'],
        ];
        $default_fields['etn_ticket_price'] = [
            'label'    => esc_html__( 'Ticket Price', 'eventin' ),
            'type'     => 'number',
            'default'  => '',
            'value'    => '',
            'desc'     => esc_html__( 'Per ticket price', 'eventin' ),
            'priority' => 1,
            'step'     => 0.01,
            'required' => true,
            'attr'     => ['class' => 'etn-label-item'],
        ];
        $default_fields['etn_event_socials'] = [
            'label'    => esc_html__( 'Social', 'eventin' ),
            'type'     => 'social_reapeater',
            'default'  => '',
            'value'    => '',
            'options'  => [
                'facebook' => [
                    'label'      => esc_html__( 'Facebook', 'eventin' ),
                    'icon_class' => '',
                ],
                'twitter'  => [
                    'label'      => esc_html__( 'Twitter', 'eventin' ),
                    'icon_class' => '',
                ],
            ],
            'desc'     => esc_html__( 'Add multiple social icon', 'eventin' ),
            'attr'     => ['class' => ''],
            'priority' => 1,
            'required' => true,
        ];

        $is_child_post =  wp_get_post_parent_id( get_the_ID() ) ? true : false;

        //override and modify existing meta fields if needed
        $this->event_fields = apply_filters( 'etn_event_fields', $default_fields);
        $this->event_fields = $this->filter_meta_fields_for_parent_child( $this->event_fields, $is_child_post );
        return $this->event_fields;
    }

    /**
     * Banner meta field function
     */
    public function banner_meta_field() {

        $this->banner_fields = apply_filters( 'etn/banner_fields/etn_metaboxs', []);

        return $this->banner_fields;
    }

    /**
     * Filter Meta Fields For Meta Boxes
     *
     * @param [type] $event_fields
     * @param [type] $is_child_post
     * @return void
     */
    public function filter_meta_fields_for_parent_child( $event_fields, $is_child_post ){

        $child_post_fields = [
            'etn_start_date', 
            'etn_start_time', 
            'etn_end_date', 
            'etn_end_time', 
            'etn_registration_deadline',
            'event_timezone', 
            'etn_ticket_availability', 
            'etn_avaiilable_tickets', 
            'etn_ticket_price'
        ];
        if( $is_child_post ){
            $new_array = array_intersect_key( $event_fields,  /* main array*/
                                                array_flip( $child_post_fields /* to be extracted */ )
                        );
            return $new_array;
        }

        return $event_fields;

    }

    /**
     * Filter Meta Boxes For Event
     *
     * @param [type] $event_fields
     * @param [type] $is_child_post
     * @return void
     */
    public function filter_meta_boxes_for_parent_child( $event_boxes, $is_child_post ){

        $child_post_fields = [
            'etn_event_settings', 
            'etn_report', 
        ];
        if( $is_child_post ){
            $new_array = array_intersect_key( $event_boxes,  /* main array*/
                                                array_flip( $child_post_fields /* to be extracted */ )
                        );
            return $new_array;
        }

        return $event_boxes;

    }

    /**
     * function etn_report_callback
     * gets the current event id,
     * gets all details of this event, calculates total sold quantity and price
     * then finally generates report
     */
    public function etn_report_callback() {
        $report_options    = get_option( "etn_event_report_etn_options" );
        $report_sorting    = isset( $report_options["event_list"] ) ? strtoupper( $report_options["event_list"] ) : "DESC";
        $ticket_qty        = get_post_meta( get_the_ID(), "etn_sold_tickets", true );
        $total_sold_ticket = isset( $ticket_qty ) ? intval( $ticket_qty ) : 0;
        $data              = \Etn\Utils\Helper::get_tickets_by_event( get_the_ID(), $report_sorting );

        if ( isset( $data['all_sales'] ) && is_array( $data['all_sales'] ) && count( $data['all_sales'] ) > 0 ) {

            foreach ( $data['all_sales'] as $single_sale ) {
                ?>
                <div>
                    <div class="etn-report-row">
                    <strong ><?php echo esc_html__( "invoice no.", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->invoice ); ?>
                    <strong class="etn-report-cell"><?php echo esc_html__( "total qty:", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->single_sale_meta ); ?>
                    <strong class="etn-report-cell"><?php echo esc_html__( "total amount:", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->event_amount ); ?>
                    <strong class="etn-report-cell"><?php echo esc_html__( "email:", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->email ); ?>
                    <strong class="etn-report-cell"><?php echo esc_html__( "status:", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->status ); ?>
                    <strong class="etn-report-cell"><?php echo esc_html__( "payment type:", "eventin" ); ?></strong> <?php echo esc_html( $single_sale->payment_gateway ); ?>
                    </div>
                </div>
                <hr>
                <?php
            }

        }

        ?>
        <div>
          <strong><?php echo esc_html__( "Total tickets sold:", "eventin" ); ?></strong> <?php echo esc_html( $total_sold_ticket ); ?>
        </div>
        <div>
          <strong><?php echo esc_html__( "Total price sold:", "eventin" ); ?></strong> <?php echo isset( $data['total_sale_price'] ) ? esc_html( $data['total_sale_price'] ) : 0; ?>
        </div>
        <?php
    }
}
