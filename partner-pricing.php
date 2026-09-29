<?php
/**
 * Partner pricing calculator: [partner_pricing partner="dogco"]
 *
 * Only the interactive part of the Unlimited card: company size, billing,
 * the price (list price struck through, member price, "You save") and the
 * IntegrityFirst Unlimited add-on switch. Everything around it (cards,
 * features, buttons, copy) is built with Beaver Builder modules on the page.
 *
 * The initial state is rendered server-side, so prices are right before any
 * JS runs; js/partner-pricing.js recalculates from the same config.
 */

if (!defined('ABSPATH')) exit;

/**
 * Discovered Unlimited list prices in USD. Same values as js/pricing-widget.js.
 * null = custom pricing.
 */
function tht_pricing_tiers() {
    return array(
        array('key' => '1-25',     'label' => '1–25 Employees',     'monthly' => 140,  'annual' => 1400),
        array('key' => '26-50',    'label' => '26–50 Employees',    'monthly' => 299,  'annual' => 2990),
        array('key' => '51-100',   'label' => '51–100 Employees',   'monthly' => 399,  'annual' => 3990),
        array('key' => '101-200',  'label' => '101–200 Employees',  'monthly' => 549,  'annual' => 5490),
        array('key' => '201-300',  'label' => '201–300 Employees',  'monthly' => 649,  'annual' => 6490),
        array('key' => '301-500',  'label' => '301–500 Employees',  'monthly' => 749,  'annual' => 7500),
        array('key' => '501-1000', 'label' => '501–1000 Employees', 'monthly' => 1149, 'annual' => 11500),
        array('key' => '1000+',    'label' => '1000+ Employees',    'monthly' => null, 'annual' => null),
    );
}

/** Per-partner settings. Add a partner by adding an entry here. */
function tht_partner_pricing_config() {
    return array(
        'dogco' => array(
            'discount_pct'   => 10,  // member discount on every paid price
            'addon_multiple' => 2,   // Unlimited + IntegrityFirst Unlimited = list price x this
            'member_label'   => 'DogCo Member Price',
            'member_pill'    => 'Extra 10% Off',
            'annual_note'    => 'Save 20% Annually',
            'addon_title'    => 'Add IntegrityFirst Unlimited',
            'addon_text'     => 'Unlimited integrity assessments.',
            // Buttons on the page that get data-size / data-billing / data-addon.
            'cta_selector'   => '.unlimited-plan',
        ),
    );
}

/** Member price in cents. Integer math so e.g. 14.75 x 90% rounds to 13.28, not 13.27. */
function tht_pp_member_cents($list_dollars, $discount_pct) {
    $cents = (int) round($list_dollars * 100);
    return (int) round($cents * (100 - $discount_pct) / 100);
}

/** $1,260 or $269.10 — cents only when there are any. */
function tht_pp_money($cents) {
    $out = '$' . number_format(floor($cents / 100));
    $rest = $cents % 100;
    return $rest ? $out . '.' . str_pad((string) $rest, 2, '0', STR_PAD_LEFT) : $out;
}

function partner_pricing_register_assets() {
    wp_register_style('partner-pricing-css', plugin_dir_url(__FILE__) . 'css/partner-pricing.css', array(), '1.1.0');
    wp_register_script('partner-pricing-js', plugin_dir_url(__FILE__) . 'js/partner-pricing.js', array(), '1.1.0', true);
}
add_action('wp_enqueue_scripts', 'partner_pricing_register_assets');

function partner_pricing_shortcode($atts) {
    $atts    = shortcode_atts(array('partner' => ''), $atts, 'partner_pricing');
    $configs = tht_partner_pricing_config();
    $key     = sanitize_key($atts['partner']);
    if (!isset($configs[$key])) {
        return current_user_can('edit_posts') ? '<p><strong>[partner_pricing]:</strong> unknown partner "' . esc_html($atts['partner']) . '".</p>' : '';
    }
    $c = $configs[$key];

    static $instance = 0;
    $uid = 'tpp-' . $key . '-' . (++$instance);

    wp_enqueue_style('partner-pricing-css');
    wp_enqueue_script('partner-pricing-js');

    $tiers = tht_pricing_tiers();
    $pct   = (int) $c['discount_pct'];

    // Initial state: first tier, monthly, add-on off.
    $list   = (int) round($tiers[0]['monthly'] * 100);
    $member = tht_pp_member_cents($tiers[0]['monthly'], $pct);

    $js_config = array(
        'discountPct'   => $pct,
        'addonMultiple' => (float) $c['addon_multiple'],
        'ctaSelector'   => $c['cta_selector'],
        'tiers'         => $tiers,
    );

    ob_start();
    ?>
<div class="tpp tpp--<?php echo esc_attr($key); ?>" data-tpp data-config="<?php echo esc_attr(wp_json_encode($js_config)); ?>">
    <div class="tpp-controls">
        <label class="tpp-field">
            <span class="tpp-eyebrow tpp-eyebrow--dark">Company Size</span>
            <span class="tpp-select">
                <select data-tpp-size>
                    <?php foreach ($tiers as $i => $t) : ?>
                        <option value="<?php echo esc_attr($t['key']); ?>"<?php selected($i, 0); ?>><?php echo esc_html($t['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </label>
        <div class="tpp-field">
            <span class="tpp-eyebrow tpp-eyebrow--dark" id="<?php echo esc_attr($uid); ?>-billing">Billing</span>
            <div class="tpp-segment" role="group" aria-labelledby="<?php echo esc_attr($uid); ?>-billing">
                <button type="button" data-tpp-billing="monthly" aria-pressed="true">Monthly</button>
                <button type="button" data-tpp-billing="annual" aria-pressed="false">Annually</button>
            </div>
        </div>
    </div>

    <div class="tpp-price-block" aria-live="polite">
        <div data-tpp-priced>
            <p class="tpp-eyebrow tpp-eyebrow--accent tpp-annual-note" data-tpp-annual-note hidden><?php echo esc_html($c['annual_note']); ?></p>
            <p class="tpp-regular">Regularly <s data-tpp-regular><?php echo esc_html(tht_pp_money($list)); ?></s><span data-tpp-period>/month</span></p>
            <p class="tpp-now"><span class="tpp-amount" data-tpp-member><?php echo esc_html(tht_pp_money($member)); ?></span><span class="tpp-per" data-tpp-period>/month</span></p>
            <div class="tpp-member"><span class="tpp-eyebrow"><?php echo esc_html($c['member_label']); ?></span><span class="tpp-pill"><?php echo esc_html($c['member_pill']); ?></span></div>
            <p class="tpp-note">You save<span data-tpp-save-extra hidden> an additional</span> <span data-tpp-save><?php echo esc_html(tht_pp_money($list - $member)); ?></span><span data-tpp-period>/month</span></p>
        </div>
        <div data-tpp-custom hidden>
            <p class="tpp-amount tpp-amount--custom">Custom Pricing</p>
            <p class="tpp-note">Contact us to find your plan.</p>
        </div>
    </div>

    <div class="tpp-addon">
        <div class="tpp-addon-text">
            <p class="tpp-eyebrow tpp-eyebrow--dark" id="<?php echo esc_attr($uid); ?>-addon"><?php echo esc_html($c['addon_title']); ?></p>
            <p class="tpp-small"><?php echo esc_html($c['addon_text']); ?></p>
        </div>
        <button type="button" class="tpp-switch" role="switch" aria-checked="false" aria-labelledby="<?php echo esc_attr($uid); ?>-addon" data-tpp-addon><span></span></button>
    </div>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('partner_pricing', 'partner_pricing_shortcode');
