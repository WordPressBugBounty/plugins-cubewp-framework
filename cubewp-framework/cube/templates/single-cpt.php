<?php
/**
 * The template for displaying all cubewp post type's single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package CubeWP
 */

get_header();
global $cubewp_frontend,$cwpOptions;

$cubewp_frontend->cubewp_post_notification();

if (CubeWp_Theme_Builder::is_cubewp_theme_builder_active('single')) {
    CubeWp_Theme_Builder::do_cubewp_theme_builder('single');
 }else {
    $single = $cubewp_frontend->single();
    wp_enqueue_style('single-cpt-styles');
    ?>
        <div class="cwp-cpt-single-container-outer">
            <div class="cwp-container">
                 <?php echo $single->get_post_featured_image(); ?>
                <div class="cwp-row cwp-cpt-single-content">
                    <div class="cwp-col-12 cwp-col-lg-8">
                        <div class="cwp-single-title-container cwp-row">
                          <div class="cwp-col-12 cwp-col-lg-8">
                            <h1 class="cwp-single-title"><?php echo get_the_title(get_the_ID()); ?></h1>
                          </div>
                            <div class="cwp-col-12 cwp-col-lg-4">
                                <div class="cwp-single-quick-actions">
                                    <?php
                                    if($cwpOptions['post_type_share_button']=='1'){
                                        echo do_shortcode( '[cubewp_post_share]' );
                                    }?>
                                    <?php
                                    if($cwpOptions['post_type_save_button']=='1'){
                                        echo do_shortcode( '[cubewp_post_save]' );
                                    }?>
                                </div>
                            </div>
                        </div>
                        <?php
                        $post_dsc = get_the_content(get_the_ID());
                        if(!empty($post_dsc)){
                        ?>
                        <div class="cwp-single-des"><?php the_content(); ?></div>
                        <?php } ?>
                        <div class="cwp-single-groups">
                            <?php $single->get_single_content_area(); ?>
                        </div>
                    </div>
                    <div class="cwp-col-12 cwp-col-lg-4">
                        <div class="cwp-right-single-groups">
                            <?php $single->get_single_sidebar_area(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php    
}
$cubewp_frontend->cubewp_post_confirmation();

get_footer();