<?php
    // Enqueue assets (CSS and JavaScript) once per page
    function render_bundle_widget($atts, $content = null) {
    // Enqueue the assets
    wp_enqueue_style('bundle-widget-css');
    wp_enqueue_script('bundle-widget-js');

    // Generate a unique ID for the widget
    static $instance_count = 0;
    $instance_count++;
    $unique_id = 'bundle_widget_' . $instance_count;

    ob_start();
    ?>
<div id="<?php echo esc_attr($unique_id); ?>" class="bundle-widget">
    <form>
        <div class="bundle-type" id="bundleType_<?php echo esc_attr($unique_id); ?>">
            <input type="radio" id="skill_<?php echo esc_attr($unique_id); ?>"
                name="billingCycle_<?php echo esc_attr($unique_id); ?>" value="skill" checked>
            <label for="skill_<?php echo esc_attr($unique_id); ?>">Essentials</label>

            <input type="radio" id="inDepth_<?php echo esc_attr($unique_id); ?>"
                name="billingCycle_<?php echo esc_attr($unique_id); ?>" value="inDepth">
            <label for="inDepth_<?php echo esc_attr($unique_id); ?>">Advanced</label>
            <span class="active-line"></span>
        </div>
        <select id="bundleQuantity_<?php echo esc_attr($unique_id); ?>"
            name="bundleQuantity_<?php echo esc_attr($unique_id); ?>">
            <option value="10">10</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
            <option value="1250">1250</option>
        </select>
    </form>
    <div class="bundle-total">500</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBundleWidget('<?php echo esc_js($unique_id); ?>');
});
</script>
<?php
    return ob_get_clean();
}

// Shortcode function to display the bundle widget
function register_bundle_widget_shortcode() {
    add_shortcode('bundle_widget', 'render_bundle_widget');
}
add_action('init', 'register_bundle_widget_shortcode');


function bundle_widget_register_assets() {
    wp_register_style('bundle-widget-css', plugin_dir_url(__FILE__) . 'css/bundle-widget.css', array(), '1.0.0');
    wp_register_script('bundle-widget-js', plugin_dir_url(__FILE__) . 'js/bundle-widget.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'bundle_widget_register_assets');