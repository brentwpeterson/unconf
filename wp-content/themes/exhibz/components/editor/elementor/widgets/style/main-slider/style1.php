   <!-- banner start-->
   <section class="main-slider owl-carousel" data-controls="<?php echo esc_attr($slide_controls); ?>">
         <?php foreach ($exhibz_slider as $key => $value): ?>
               
            <div class="banner-item overlay" style="background-image:url(<?php echo is_array($value["exhibz_slider_bg_image"])?$value["exhibz_slider_bg_image"]["url"]:''; ?>)">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-11 mx-auto">
                            <div class="banner-content-wrap text-center">
                                <?php if($value["exhibz_show_title_shap"]=="yes"): ?>
                                <img class="title-shap-img" src="<?php echo esc_url( EXHIBZ_IMG.'/shap/title-white.png' ); ?> " alt="<?php esc_attr_e('shape','exhibz'); ?> ">
                            <?php endif; ?>
                                <p class="banner-info"><?php echo esc_html($value["exhibz_slider_title_top"]); ?></p>
                                <h1 class="banner-title"><?php echo esc_html($value["exhibz_slider_title"]); ?></h1>

                                <p class="banner-desc">
                                <?php echo wp_kses_post($value["exhibz_slider_description"]); ?>
                                </p>
                                <!-- Countdown end -->
                                <div class="ts-banner-btn">
                                    <?php if($value["exhibz_button_one_text"]!=''): ?> 
                                    <a href="<?php echo esc_url($value["exhibz_button_one"]["url"]); ?>" class="btn"  target="<?php echo esc_attr($value["exhibz_button_one"]["is_external"] == "on" ? "_blank" : '_self'); ?>" rel="<?php echo esc_attr($value["exhibz_button_one"]["nofollow"] == "on" ? "" : 'nofollow'); ?>"><?php echo esc_html($value["exhibz_button_one_text"]); ?></a>
                                    <?php endif; ?>
                                    <?php if($value["exhibz_button_two_text"]!=''): ?> 
                                    <a href="<?php echo esc_url($value["exhibz_button_two"]["url"]); ?>" class="btn fill" target="<?php echo esc_attr($value["exhibz_button_two"]["is_external"] == "on" ? "_blank" : '_self'); ?>" rel="<?php echo esc_attr($value["exhibz_button_two"]["nofollow"] == "on" ? "" : 'nofollow'); ?>"><?php echo esc_html($value["exhibz_button_two_text"]); ?></a>
                                    <?php endif; ?>
                                </div>

                            </div>
                            <!-- Banner content wrap end -->
                        </div><!-- col end-->

                    </div><!-- row end-->
                </div>
                <!-- Container end -->
            </div><!-- banner item end-->
         <?php endforeach; ?>
      </section>
      <!-- banner end-->