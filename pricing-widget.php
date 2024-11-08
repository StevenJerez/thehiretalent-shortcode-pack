<?php
/**
 * Plugin Name: Pricing Widget
 * Description: A shortcode-based pricing widget with flexible options to display multiple sections.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Enqueue CSS and JS once per page
function pricing_widget_enqueue_assets() {
    static $loaded = false;
    if (!$loaded) {
        wp_enqueue_style('pricing-widget-css', plugin_dir_url(__FILE__) . 'css/pricing-widget.css');
        wp_enqueue_script('pricing-widget-js', plugin_dir_url(__FILE__) . 'js/pricing-widget.js', array('jquery'), null, true);
        $loaded = true;
    }
}

// Shortcode function to display the pricing widget or its parts
function pricing_widget_shortcode($atts) {
    pricing_widget_enqueue_assets(); // Load assets
    
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'section' => 'full', // Default to 'full'
    ), $atts, 'pricing_widget');

    // Split sections into an array
    $sections = array_map('trim', explode(',', $atts['section']));

    ob_start();

    // Display the requested sections
    if (in_array('full', $sections)) {
        // If "full" is specified, show the entire widget and ignore other sections
        ?>
<div class="pricing-widget">
    <div id="monthlyOrYearly">
        <input type="radio" id="annually" name="billingCycle" value="annually" checked>
        <label for="annually">Annually <span>Save 20%</span></label>
        <input type="radio" id="monthly" name="billingCycle" value="monthly">
        <label for="monthly">Monthly</label>
    </div>
    <div class="pricing-row">
        <div class="pricing-col">
            <form action="">
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
            <div class="pricing-total">500</div>
        </div>
        <div class="pricing-col">
            <div class="pricing-complete"></div>
        </div>
    </div>
</div>
<?php
    } else {
        // Render specific sections
        if (in_array('billing', $sections)) {
            ?>
<div id="monthlyOrYearly">
    <input type="radio" id="annually" name="billingCycle" value="annually" checked>
    <label for="annually">Annually <span>Save 20%</span></label>
    <input type="radio" id="monthly" name="billingCycle" value="monthly">
    <label for="monthly">Monthly</label>
</div>
<?php
        }

        if (in_array('employees', $sections)) {
            ?>
<form action="">
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
        }

        if (in_array('pricing', $sections)) {
            ?>
<div class="pricing-total">500</div>
<div class="pricing-complete"></div>
<?php
        }
    }

    return ob_get_clean();
}

// Register the shortcode
add_shortcode('pricing_widget', 'pricing_widget_shortcode');