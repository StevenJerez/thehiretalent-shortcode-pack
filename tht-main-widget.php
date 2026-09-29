<?php
/**
 * Plugin Name: The Hire Talent: Shortcode Pack
 * Description: A shortcode-based pricing widget with flexible options to display individual sections or the full widget.
 * Version: 1.5
 * Author: Steven Jerez THT
 * Author URI: https://github.com/StevenJerez/thehiretalent-shortcode-pack/blob/main/README.md
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Include the pricing widget
require_once plugin_dir_path(__FILE__) . 'pricing-widget.php';
// Include the sticky navbar
require_once plugin_dir_path(__FILE__) . 'pricing-sticky-navbar.php';
// Include the bundle widget
require_once plugin_dir_path(__FILE__) . 'bundle-widget.php';
// Include the partner pricing section
require_once plugin_dir_path(__FILE__) . 'partner-pricing.php';
