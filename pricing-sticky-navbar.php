<?php
function custom_sticky_nav_assets() {
        // Enqueue CSS for the sticky navbar
        wp_register_style('custom-sticky-nav-style', plugin_dir_url(__FILE__) . 'css/sticky-nav.css', array(), '1.0.1');
        // Enqueue JavaScript for the sticky navbar
        wp_register_script('custom-sticky-nav-script', plugin_dir_url(__FILE__) . 'js/sticky-nav.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'custom_sticky_nav_assets');

function custom_sticky_nav_shortcode() {
    // Load the assets
    wp_enqueue_style('custom-sticky-nav-style');
    wp_enqueue_script('custom-sticky-nav-script');

    ob_start();
    ?>
<div class="custom-navbar">
    <a href="#assessments-section">
        <input type="radio" id="assessments" name="nav" checked>
        <label for="assessments">Assessments</label>
    </a>

    <a href="#talent-section">
        <input type="radio" id="talent" name="nav">
        <label for="talent">Talent</label>
    </a>

    <a href="#talent-grader-section">
        <input type="radio" id="talent-grader" name="nav">
        <label for="talent-grader">Talent Grader</label>
    </a>

    <a href="#automation-section">
        <input type="radio" id="automation" name="nav">
        <label for="automation">Automation recruitment</label>
    </a>

    <a href="#integration-section">
        <input type="radio" id="integration" name="nav">
        <label for="integration">Integration</label>
    </a>

    <a href="#hiring-coach-section">
        <input type="radio" id="hiring-coach" name="nav">
        <label for="hiring-coach">Hiring Coach</label>
    </a>

    <!-- Green underline indicator -->
    <span class="indicator"></span>
</div>
<?php
    return ob_get_clean();
}
add_shortcode('sticky_navbar', 'custom_sticky_nav_shortcode');