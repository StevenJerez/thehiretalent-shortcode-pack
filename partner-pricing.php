<?php
/**
 * Partner pricing section: [partner_pricing partner="dogco"]
 *
 * Three plan cards (Essentials, Unlimited, IntegrityFirst) with the partner's
 * member discount applied. Unlimited has company size, billing and an
 * IntegrityFirst Unlimited add-on. Prices are rendered server-side for the
 * initial state (so they are right before any JS runs) and recalculated by
 * js/partner-pricing.js from the same config.
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
            'discount_pct'   => 10,     // member discount on every paid price
            'if_price'       => 14.75,  // IntegrityFirst list price per assessment
            'addon_multiple' => 2,      // Unlimited + IntegrityFirst Unlimited = list price x this
            'eyebrow'        => 'DogCo Member Pricing',
            'heading'        => 'Exclusive pricing for<br> DogCo Launch members.',
            'subheading'     => 'Choose the hiring solution that fits your business and get an extra 10% off Discovered pricing as a DogCo Launch member.',
            'badge_label'    => 'DogCo Member Benefit',
            'badge_text'     => 'Extra 10% Off Discovered Pricing',
            'member_label'   => 'DogCo Member Price',
            'member_pill'    => 'Extra 10% Off',
            'annual_note'    => 'Save 20% Annually',
            'callout_title'  => 'More flexibility for DogCo members.',
            'callout_text'   => 'DogCo Launch members receive an extra 10% off Discovered pricing, plus access to monthly billing options not normally available to companies of this size.',
            'cta'            => array(
                // Classes are what the page's UABB modals ("Display on: Custom") listen for.
                'essentials' => array('text' => 'Get Started',       'class' => 'free-plan'),
                'unlimited'  => array('text' => 'Get Unlimited',     'class' => 'unlimited-plan'),
                'if'         => array('text' => 'Get IntegrityFirst', 'class' => 'free-plan'),
            ),
            'features'       => array(
                'essentials' => array(
                    '4 FREE Essential Credits and 4 FREE Advanced Assessments',
                    'Free Access to AI-Powered Candidate Assessments',
                    'Candidate Scheduling',
                    'Access to Candidate Scorecards',
                    'Performance Management Tool',
                    'Unlimited Interview Guides',
                    'Seamless Background Checks with Checkr Integration',
                    'Kingsley AI Hiring Assistant',
                    'Free Applicant Tracking System',
                    'Unlimited Users',
                ),
                'unlimited'  => array(
                    'Unlimited Pre-Employment Assessments & Predictive Analytics',
                    'Market Openings Across All Major Job Boards',
                    'Unlimited Assessments, Video Interviews & Reference Checking',
                    'Automated Hiring Process',
                    'Email & SMS Integration',
                    'All 44+ Features Unlimited Use',
                    'Dedicated Hiring Coach & Implementor',
                ),
                'if'         => array(
                    '10% off every assessment for DogCo members',
                    '8–10 minute integrity assessment',
                    'Available in 27+ languages',
                    'Mobile-friendly hiring workflow',
                    'Instant Qualified or Unqualified results',
                    'Essentials features included',
                ),
            ),
        ),
    );
}

/** Member price in cents. Integer math so 14.75 x 90% rounds to 13.28, not 13.27. */
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

/** Paw print in the style of Font Awesome's "paw": four tilted toes over a wide pad. */
function tht_pp_paw($class = '') {
    return '<svg class="tpp-paw ' . esc_attr($class) . '" viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true" focusable="false">'
        . '<ellipse cx="3.9" cy="10.6" rx="2.2" ry="2.7" transform="rotate(-22 3.9 10.6)"/>'
        . '<ellipse cx="8.5" cy="5.4" rx="2.4" ry="3" transform="rotate(-8 8.5 5.4)"/>'
        . '<ellipse cx="15.5" cy="5.4" rx="2.4" ry="3" transform="rotate(8 15.5 5.4)"/>'
        . '<ellipse cx="20.1" cy="10.6" rx="2.2" ry="2.7" transform="rotate(22 20.1 10.6)"/>'
        . '<path d="M12 11.3c-2.4 0-4.3 1.9-5.6 4-.9 1.4-2.3 2.5-2.3 4.2 0 1.8 1.4 3.1 3.2 3.1 1.9 0 3.1-.9 4.7-.9s2.8.9 4.7.9c1.8 0 3.2-1.3 3.2-3.1 0-1.7-1.4-2.8-2.3-4.2-1.3-2.1-3.2-4-5.6-4z"/></svg>';
}

function tht_pp_features($items) {
    $html = '<ul class="tpp-features">';
    foreach ($items as $item) {
        $html .= '<li>' . tht_pp_paw() . '<span>' . esc_html($item) . '</span></li>';
    }
    return $html . '</ul>';
}

function partner_pricing_register_assets() {
    wp_register_style('partner-pricing-css', plugin_dir_url(__FILE__) . 'css/partner-pricing.css', array(), '1.0.3');
    wp_register_script('partner-pricing-js', plugin_dir_url(__FILE__) . 'js/partner-pricing.js', array(), '1.0.3', true);
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
    $first         = $tiers[0];
    $u_list        = (int) round($first['monthly'] * 100);
    $u_member      = tht_pp_member_cents($first['monthly'], $pct);
    $if_list       = (int) round($c['if_price'] * 100);
    $if_member     = tht_pp_member_cents($c['if_price'], $pct);

    $js_config = array(
        'discountPct'   => $pct,
        'addonMultiple' => (float) $c['addon_multiple'],
        'tiers'         => $tiers,
    );

    $cta = function ($which, $extra = '') use ($c) {
        return '<button type="button" class="tpp-cta ' . esc_attr($c['cta'][$which]['class'] . ' ' . $extra) . '">' . esc_html($c['cta'][$which]['text']) . '</button>';
    };
    $member_row = '<div class="tpp-member"><span class="tpp-eyebrow">' . esc_html($c['member_label']) . '</span><span class="tpp-pill">' . esc_html($c['member_pill']) . '</span></div>';

    ob_start();
    ?>
<section class="tpp tpp--<?php echo esc_attr($key); ?>" data-tpp data-config="<?php echo esc_attr(wp_json_encode($js_config)); ?>">
    <header class="tpp-head">
        <p class="tpp-eyebrow tpp-eyebrow--accent"><?php echo esc_html($c['eyebrow']); ?></p>
        <h2 class="tpp-title"><?php echo wp_kses($c['heading'], array('br' => array())); ?></h2>
        <p class="tpp-sub"><?php echo esc_html($c['subheading']); ?></p>
        <p class="tpp-badge">
            <?php echo tht_pp_paw(); ?>
            <span class="tpp-badge-label"><?php echo esc_html($c['badge_label']); ?></span>
            <span class="tpp-badge-sep" aria-hidden="true"></span>
            <span class="tpp-badge-text"><?php echo esc_html($c['badge_text']); ?></span>
        </p>
    </header>

    <div class="tpp-cards">
        <article class="tpp-card tpp-card--essentials">
            <div class="tpp-card-head">
                <p class="tpp-brand">Discovered</p>
                <h3 class="tpp-plan">Essentials Plan</h3>
            </div>
            <div class="tpp-price-block">
                <p class="tpp-amount">$0</p>
                <p class="tpp-note">Included at no cost</p>
            </div>
            <?php echo $cta('essentials'); ?>
            <hr class="tpp-rule">
            <p class="tpp-eyebrow tpp-eyebrow--dark">Features</p>
            <?php echo tht_pp_features($c['features']['essentials']); ?>
        </article>

        <article class="tpp-card tpp-card--unlimited" data-tpp-unlimited>
            <div class="tpp-card-head">
                <p class="tpp-brand">Discovered</p>
                <h3 class="tpp-plan">Unlimited Plan</h3>
            </div>

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
                    <p class="tpp-regular">Regularly <s data-tpp-regular><?php echo esc_html(tht_pp_money($u_list)); ?></s><span data-tpp-period>/month</span></p>
                    <p class="tpp-now"><span class="tpp-amount" data-tpp-member><?php echo esc_html(tht_pp_money($u_member)); ?></span><span class="tpp-per" data-tpp-period>/month</span></p>
                    <?php echo $member_row; ?>
                    <p class="tpp-note">You save<span data-tpp-save-extra hidden> an additional</span> <span data-tpp-save><?php echo esc_html(tht_pp_money($u_list - $u_member)); ?></span><span data-tpp-period>/month</span></p>
                </div>
                <div data-tpp-custom hidden>
                    <p class="tpp-amount tpp-amount--custom">Custom Pricing</p>
                    <p class="tpp-note">Contact us to find your plan.</p>
                </div>
            </div>

            <div class="tpp-addon">
                <div class="tpp-addon-text">
                    <p class="tpp-eyebrow tpp-eyebrow--dark" id="<?php echo esc_attr($uid); ?>-addon">Add IntegrityFirst Unlimited</p>
                    <p class="tpp-small">Unlimited integrity assessments.</p>
                </div>
                <button type="button" class="tpp-switch" role="switch" aria-checked="false" aria-labelledby="<?php echo esc_attr($uid); ?>-addon" data-tpp-addon><span></span></button>
            </div>

            <?php echo $cta('unlimited', 'tpp-cta--primary'); ?>
            <hr class="tpp-rule">
            <p class="tpp-eyebrow tpp-eyebrow--dark">Features</p>
            <p class="tpp-lead">Everything in Essentials, plus:</p>
            <?php echo tht_pp_features($c['features']['unlimited']); ?>
        </article>

        <article class="tpp-card tpp-card--if">
            <div class="tpp-card-head">
                <p class="tpp-brand">Discovered</p>
                <h3 class="tpp-plan">IntegrityFirst</h3>
                <p class="tpp-eyebrow">Pay Per Assessment</p>
            </div>
            <div class="tpp-price-block">
                <p class="tpp-regular">Regularly <s><?php echo esc_html(tht_pp_money($if_list)); ?></s>/assessment</p>
                <p class="tpp-now"><span class="tpp-amount"><?php echo esc_html(tht_pp_money($if_member)); ?></span><span class="tpp-per">/assessment</span></p>
                <?php echo $member_row; ?>
                <p class="tpp-note">You save <?php echo esc_html(tht_pp_money($if_list - $if_member)); ?>/assessment</p>
            </div>
            <?php echo $cta('if'); ?>
            <hr class="tpp-rule">
            <div class="tpp-included">
                <p class="tpp-eyebrow">Essentials Included</p>
                <p class="tpp-small">Core Discovered hiring tools included.</p>
            </div>
            <p class="tpp-eyebrow tpp-eyebrow--dark">Features</p>
            <?php echo tht_pp_features($c['features']['if']); ?>
        </article>
    </div>

    <aside class="tpp-callout">
        <?php echo tht_pp_paw('tpp-callout-paw'); ?>
        <div>
            <p class="tpp-callout-title"><?php echo esc_html($c['callout_title']); ?></p>
            <p class="tpp-callout-text"><?php echo esc_html($c['callout_text']); ?></p>
        </div>
    </aside>
</section>
    <?php
    return ob_get_clean();
}
add_shortcode('partner_pricing', 'partner_pricing_shortcode');
