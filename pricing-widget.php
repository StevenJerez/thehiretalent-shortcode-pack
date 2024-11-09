<?php
/**
 * Plugin Name: Pricing Widget
 * Description: A shortcode-based pricing widget with flexible options to display individual sections or the full widget.
 * Version: 1.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Enqueue assets (CSS and JavaScript) once per page
function pricing_widget_enqueue_assets() {
    static $loaded = false;
    if (!$loaded) {
        wp_enqueue_style('pricing-widget-css', plugin_dir_url(__FILE__) . 'css/pricing-widget.css');
        wp_enqueue_script('pricing-widget-js', plugin_dir_url(__FILE__) . 'js/pricing-widget.js', array('jquery'), null, true);

        // Retrieve saved pricing data from the options
        $pricing_data = get_option('pricing_widget_data', []);

        // Localize the pricing data for JavaScript
        wp_localize_script('pricing-widget-js', 'pricingWidgetData', $pricing_data);

        $loaded = true;
    }
}
add_action('wp_enqueue_scripts', 'pricing_widget_enqueue_assets');

// Render sections of the pricing widget
function render_pricing_widget_section($section) {
    switch ($section) {
        case 'billing':
            ?>
<div id="monthlyOrYearly" class="pricing-widget-section billing-section">
    <input type="radio" id="annually" name="billingCycle" value="annually" checked>
    <label for="annually">Annually <span>Save 20%</span></label>
    <input type="radio" id="monthly" name="billingCycle" value="monthly">
    <label for="monthly">Monthly</label>
</div>
<?php
            break;

        case 'employees':
            ?>
<form action="" class="pricing-widget-section employees-section">
    <select id="numberOfEmployees" name="numberOfEmployees">
        <option value="1-25">1 - 25 employees</option>
        <option value="26-50">26 - 50 employees</option>
        <option value="51-100">51 - 100 employees</option>
        <option value="101-200">101 - 200 employees</option>
        <option value="201-300">201 - 300 employees</option>
        <option value="301-500">301 - 500 employees</option>
        <option value="501-1000">501 - 1000 employees</option>
        <option value="1000+">1000+ employees</option>
    </select>
</form>
<?php
            break;

        case 'pricing':
            ?>
<div class="pricing-widget-section pricing-total">500</div>
<?php
            break;

        case 'complete':
            ?>
<div class="pricing-widget-section pricing-complete"></div>
<?php
            break;
    }
}

// Shortcode function to display the pricing widget or its parts
function pricing_widget_shortcode($atts) {
    pricing_widget_enqueue_assets(); // Load assets

    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'section' => 'full', // Default to 'full'
    ), $atts, 'pricing_widget');

    ob_start();

    echo '<div class="pricing-widget">';

    // Split sections into an array
    $sections = array_map('trim', explode(',', $atts['section']));
    $render_full_widget = in_array('full', $sections);

    // Determine if the layout should include a pricing row
    $has_pricing_row = $render_full_widget || in_array('employees', $sections) || in_array('pricing', $sections);

    if ($render_full_widget || in_array('billing', $sections)) {
        render_pricing_widget_section('billing');
    }

    // Render first pricing row with employees and pricing total
    if ($has_pricing_row) {
        echo '<div class="pricing-row">';
        
        echo '<div class="pricing-col">';
        if ($render_full_widget || in_array('employees', $sections)) {
            render_pricing_widget_section('employees');
        }
        if ($render_full_widget || in_array('pricing', $sections)) {
            render_pricing_widget_section('pricing');
        }
        echo '</div>'; // End of first pricing row column
        
        // Render second pricing row with pricing complete
        if ($render_full_widget || in_array('complete', $sections)) {
            echo '<div class="pricing-col">';
            render_pricing_widget_section('complete');
            echo '</div>'; // End of second pricing row column
        }

        echo '</div>'; // End of first pricing row
    }


    echo '</div>'; // End of main widget container

    return ob_get_clean();
}

// Register the shortcode
add_shortcode('pricing_widget', 'pricing_widget_shortcode');