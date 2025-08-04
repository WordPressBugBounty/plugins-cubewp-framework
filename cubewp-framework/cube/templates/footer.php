<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if ( is_singular( 'cubewp-tb' ) ) {
    $template = get_post_meta(get_the_ID(), 'template_type', true);
    if($template != 'footer'){
        echo '<footer>';
        do_action( 'cubewp/theme_builder/footer' );
        echo '</footer>';
    }
}else{
    echo '<footer>';
    do_action( 'cubewp/theme_builder/footer' );
    echo '</footer>';
}
?>
<?php wp_footer(); ?>
</body>
</html>