<?php
    $category_limit = $exhibz_event_category_settings['category-options']['category_limit'];
    $categories_id  = $exhibz_event_category_settings['category-options']['categories_id'];
    $post_sort_by  = $exhibz_event_category_settings['category-options']['post_sort_by'];
    $hide_empty     = $exhibz_event_category_settings['hide_empty']=='yes'?'1':'0';
    $taxonomy       = 'etn_category';

   
    if(is_array($categories_id) && !empty($categories_id)){
        $cats = $categories_id;
    }else{
        $args_cat = array(
            'taxonomy'     => $taxonomy,
            'number' => $category_limit,
            'hide_empty' => $hide_empty,
            'orderby'    => 'post_date',
            'order'    => $post_sort_by,
        );
        $cats = get_categories( $args_cat );
    }
  
   
    ?>
  <div class="ts-event-category-slider owl-carousel">
        <?php  foreach($cats as $value){ 
            $term = get_term($value,$taxonomy); 
           
            if ( defined( 'FW' ) ) {
            $img_id = fw_get_db_term_option($term->term_id, $taxonomy, 'event_category_featured_img');
            $img_url = $img_id['url'];
            $term_link = get_term_link($term->slug, $taxonomy);
    
        ?>
            <div class="event-slider-item">
                
                <div class="cat-content">
                    
                    <h3 class="ts-title"> <a href="<?php echo esc_url($term_link); ?>"><?php echo esc_html($term->name); ?></a>  </h3>
                </div>

                <?php if($img_url !=''):  ?>
                    <div class="cat-bg">
                        <a class="cat-link" href="<?php echo esc_url($term_link); ?>">
                            <img src="<?php echo esc_url($img_url); ?>" alt="">
                        </a>
                    </div>
                <?php endif; ?>
            </div> 
            <?php 
            }
        } ?>
    </div>
   <?php 
