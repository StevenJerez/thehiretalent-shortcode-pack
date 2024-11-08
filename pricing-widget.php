<?php
/**
 * Plugin Name: Pricing Widget
 * Description: A shortcode-based pricing widget with flexible options to display multiple sections.
 * Version: 1.6
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add the settings page in the WordPress dashboard
function pricing_widget_admin_menu() {
    add_menu_page(
        'Pricing Widget Settings',
        'Pricing Widget',
        'manage_options',
        'pricing-widget-settings',
        'pricing_widget_settings_page',
        'dashicons-editor-table', // Icon for the menu item
        100
    );
}
add_action('admin_menu', 'pricing_widget_admin_menu');

// Render the settings page
function pricing_widget_settings_page() {
    ?>
<div class="wrap">
    <h1>Pricing Widget Settings</h1>
    <form method="post" action="options.php">
        <?php
            // Display settings fields and save button
            settings_fields('pricing_widget_settings');  // Make sure this matches the option group name in register_setting
            do_settings_sections('pricing-widget-settings');
            submit_button();
            ?>
    </form>
</div>
<?php
}

// Register settings, section, and fields
function pricing_widget_register_settings() {
    // Register the main settings option in the database
    register_setting('pricing_widget_settings', 'pricing_widget_data');  // 'pricing_widget_options' is the options group

    // Add a settings section
    add_settings_section(
        'pricing_widget_main_section',
        'Employee Range Pricing',
        'pricing_widget_section_description',  // Optional callback for section description
        'pricing-widget-settings'
    );

    $ranges = [
        '1-25' => '1 - 25 Employees',
        '26-50' => '26 - 50 Employees',
        '51-100' => '51 - 100 Employees',
        '101-200' => '101 - 200 Employees',
        '201-300' => '201 - 300 Employees',
        '301-500' => '301 - 500 Employees',
        '501-1000' => '501 - 1000 Employees',
        '1000+' => '1000+ Employees',
    ];

    foreach ($ranges as $key => $label) {
        add_settings_field(
            "pricing_widget_{$key}",
            $label,
            'pricing_widget_render_field',
            'pricing-widget-settings',
            'pricing_widget_main_section',
            ['key' => $key]
        );
    }
}
add_action('admin_init', 'pricing_widget_register_settings');

// Optional section description
function pricing_widget_section_description() {
    echo '<p>Set pricing for each employee range and activate or deactivate specific billing cycles.</p>';
}

// Render the input fields for each employee range
function pricing_widget_render_field($args) {
    $key = $args['key'];
    $options = get_option('pricing_widget_data');
    $annual = esc_attr($options[$key]['annually'] ?? '');
    $quarterly = esc_attr($options[$key]['quarterly'] ?? '');
    $monthly = esc_attr($options[$key]['monthly'] ?? '');

    $annual_active = isset($options[$key]['annually_active']) ? 'checked' : '';
    $quarterly_active = isset($options[$key]['quarterly_active']) ? 'checked' : '';
    $monthly_active = isset($options[$key]['monthly_active']) ? 'checked' : '';

    echo "<label>Annually: <input type='text' name='pricing_widget_data[$key][annually]' value='$annual' />";
    echo "<input type='checkbox' name='pricing_widget_data[$key][annually_active]' $annual_active /> Active</label><br>";

    echo "<label>Quarterly: <input type='text' name='pricing_widget_data[$key][quarterly]' value='$quarterly' />";
    echo "<input type='checkbox' name='pricing_widget_data[$key][quarterly_active]' $quarterly_active /> Active</label><br>";

    echo "<label>Monthly: <input type='text' name='pricing_widget_data[$key][monthly]' value='$monthly' />";
    echo "<input type='checkbox' name='pricing_widget_data[$key][monthly_active]' $monthly_active /> Active</label><br>";
}

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

// Helper function to render individual sections
function render_pricing_widget_section($section) {
    switch ($section) {
        case 'billing':
            ?>
<div id="monthlyOrYearly">
    <input type="radio" id="annually" name="billingCycle" value="annually" checked>
    <label for="annually">Annually <span>Save 20%</span></label>
    <input type="radio" id="monthly" name="billingCycle" value="monthly">
    <label for="monthly">Monthly</label>
</div>
<?php
            break;

        case 'employees':
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
            break;

        case 'pricing':
            ?>
<div class="pricing-total">500</div>
<?php
            break;

        case 'complete':
            ?>
<div class="pricing-complete"></div>
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

    // Split sections into an array
    $sections = array_map('trim', explode(',', $atts['section']));

    ob_start();

    echo '<div class="pricing-widget">';

    // Check if "full" is specified, which includes all sections
    $render_full_widget = in_array('full', $sections);

    // Render the billing section only once
    if ($render_full_widget || in_array('billing', $sections)) {
        render_pricing_widget_section('billing');
    }

    // Render employees and pricing within a row with separate columns if needed
    if ($render_full_widget || (in_array('employees', $sections) && in_array('pricing', $sections))) {
        echo '<div class="pricing-row">';
        echo '<div class="pricing-col">';
        render_pricing_widget_section('employees');
        render_pricing_widget_section('pricing');
        echo '</div>';
        echo '<div class="pricing-col">';
        render_pricing_widget_section('complete');
        echo '</div>';
        echo '</div>';
    } else {
        // Render individual sections if not using the full widget or the combined row
        if (in_array('employees', $sections)) {
            render_pricing_widget_section('employees');
        }
        if (in_array('pricing', $sections)) {
            render_pricing_widget_section('pricing');
        }
        if (in_array('complete', $sections)) {
            render_pricing_widget_section('complete');
        }
    }

    echo '</div>';

    return ob_get_clean();
}

// Register the shortcode
add_shortcode('pricing_widget', 'pricing_widget_shortcode');