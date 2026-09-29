<?php
/**
 * Restyles the #dogco-pricing row of the DogCo page (59927) to the Figma design,
 * keeping every card, text, list and button as a normal Beaver Builder module.
 * Only the Unlimited calculator is the [partner_pricing partner="dogco"] shortcode.
 *
 * Run:  wp eval-file tools/dogco-layout.php [source=/path/to/export.ser] [page=59927]
 *   source: a serialized export whose [59927]['meta']['_fl_builder_data'] is the
 *           original prod layout (local). Without it, the page's current layout is
 *           used, which on prod is the original. Run it on a draft copy first.
 */

$opts = array();
foreach ( $args ?? array() as $a ) {
	if ( false !== strpos( $a, '=' ) ) {
		list( $k, $v ) = explode( '=', $a, 2 );
		$opts[ $k ] = $v;
	}
}
$page = (int) ( $opts['page'] ?? 59927 );

if ( ! empty( $opts['source'] ) ) {
	$export = unserialize( file_get_contents( $opts['source'] ) ); // phpcs:ignore
	$data   = $export[59927]['meta']['_fl_builder_data'];
	$dset   = $export[59927]['meta']['_fl_builder_data_settings'];
} else {
	$data = get_post_meta( $page, '_fl_builder_data', true );
	$dset = get_post_meta( $page, '_fl_builder_data_settings', true );
}
if ( ! $data ) {
	WP_CLI::error( 'No layout data' );
}

// ---------------------------------------------------------------- helpers
$C = array(
	// Colors as the page already used them on prod (titles teal, prefix gray,
	// dark text, orange paws); layout and sizes follow the Figma design.
	'heading' => '#1F2937',
	'title'   => '#33b29c',
	'text'    => '#1F2937',
	'muted'   => '#4a5759',
	'brand'   => '#B0B0B0',
	'icon'    => '#ffa02b',
	'accent'  => '#33b29c',
	'tint'    => '#e7f4f4',
	'line'    => '#d6e6e4',
	'cta'     => '#ff9f2c',
	'ctaHov'  => '#f28c12',
);

// BB keeps node settings as an object whose nested fields (border, typography,
// place_content…) are plain arrays. Round-trip to arrays, cast only the top level.
$arr = function ( $v ) {
	return json_decode( wp_json_encode( $v ), true );
};
$obj = function ( $a ) {
	return (object) $a;
};

$need = function ( $id ) use ( &$data ) {
	if ( ! isset( $data[ $id ] ) ) {
		WP_CLI::error( "Node $id not found — the layout changed since this script was written." );
	}
	return $data[ $id ];
};

// Merge settings into a node; nested arrays merge into existing objects.
$set = function ( $id, $values ) use ( &$data, $need, $obj, $arr ) {
	$n = $need( $id );
	$s = array_replace_recursive( $arr( $n->settings ), $values );
	// Lists are replaced, not merged index by index.
	foreach ( $values as $k => $v ) {
		if ( is_array( $v ) && array_is_list( $v ) ) {
			$s[ $k ] = $v;
		}
	}
	$data[ $id ]->settings = $obj( $s );
};

$typo = function ( $size, $weight = 'default', $extra = array() ) {
	return array_replace_recursive(
		array(
			'font_family'    => 'Default',
			'font_weight'    => (string) $weight,
			'font_size'      => array( 'length' => (string) $size, 'unit' => 'px' ),
			'line_height'    => array( 'length' => '', 'unit' => '' ),
			'text_align'     => '',
			'letter_spacing' => array( 'length' => '', 'unit' => 'px' ),
			'text_transform' => 'none',
		),
		$extra
	);
};

$border = function ( $width, $color, $radius, $shadow = null ) {
	return array(
		'style'  => $width ? 'solid' : 'none',
		'color'  => $color,
		'width'  => array( 'top' => (string) $width, 'right' => (string) $width, 'bottom' => (string) $width, 'left' => (string) $width ),
		'radius' => array( 'top_left' => (string) $radius, 'top_right' => (string) $radius, 'bottom_left' => (string) $radius, 'bottom_right' => (string) $radius ),
		'shadow' => $shadow ?: array( 'color' => '', 'horizontal' => '', 'vertical' => '', 'blur' => '', 'spread' => '' ),
	);
};

$del = function ( $id ) use ( &$data, &$del ) {
	foreach ( $data as $nid => $n ) {
		if ( $n->parent === $id ) {
			$del( $nid );
		}
	}
	unset( $data[ $id ] );
};

$new = function ( $type, $parent, $settings ) use ( &$data, $obj, $arr ) {
	$id = substr( md5( uniqid( $type, true ) ), 0, 12 );
	$data[ $id ] = (object) array(
		'node'     => $id,
		'type'     => 'module',
		'parent'   => $parent,
		'position' => 99,
		'settings' => $obj( array_replace_recursive( $arr( FLBuilderModel::get_module_defaults( $type ) ), array( 'type' => $type ), $settings ) ),
	);
	return $id;
};

$order = function ( $parent, $ids ) use ( &$data ) {
	foreach ( $ids as $i => $id ) {
		$data[ $id ]->parent   = $parent;
		$data[ $id ]->position = $i;
	}
};

$margins0 = array( 'margin_top' => '0', 'margin_right' => '0', 'margin_bottom' => '0', 'margin_left' => '0' );

$sep = function ( $parent ) use ( $new, $C ) {
	return $new( 'separator', $parent, array(
		'color' => $C['line'], 'height' => 1, 'width' => '100', 'style' => 'solid',
		'margin_top' => '24', 'margin_bottom' => '24', 'margin_left' => '0', 'margin_right' => '0',
	) );
};

// Plan title: "DISCOVERED" + plan name (pp-heading).
$plan_head = function ( $id, $sub = '' ) use ( $set, $typo, $C ) {
	$set( $id, array(
		'prefix_tag'                => 'div',
		'prefix_text'               => 'Discovered',
		'heading_tag'               => 'h3',
		'dual_heading'              => 'no',
		'heading_alignment'         => 'center',
		'enable_link'               => 'no',
		'heading_sub_title'         => $sub ? '<p>' . $sub . '</p>' : '',
		'prefix_text_color'         => $C['brand'],
		'heading_color'             => $C['title'],
		'sub_heading_color'         => $C['muted'],
		'heading_top_margin'        => '6',
		'heading_bottom_margin'     => '0',
		'sub_heading_top_margin'    => '4',
		'sub_heading_bottom_margin' => '0',
		'prefix_typography'         => $typo( 14, 700, array( 'letter_spacing' => array( 'length' => '3' ), 'text_transform' => 'uppercase' ) ),
		'title_typography'          => $typo( 26, 700, array( 'line_height' => array( 'length' => '1.2' ), 'text_transform' => 'capitalize' ) ),
		'desc_typography'           => $typo( 11, 700, array( 'letter_spacing' => array( 'length' => '1.8' ), 'text_transform' => 'uppercase' ) ),
		'margin_top'                => '0',
		'margin_bottom'             => '0',
		// Empty = inherit the desktop value on phones.
		'margin_top_responsive'     => '',
		'margin_bottom_responsive'  => '',
	) );
};

// "FEATURES" label (pp-heading prefix only) + optional lead line in the title.
$features_head = function ( $id, $lead = '' ) use ( $set, $typo, $C ) {
	$set( $id, array(
		'prefix_tag'            => 'div',
		'prefix_text'           => 'Features',
		'heading_tag'           => 'p',
		'dual_heading'          => 'no',
		'heading_title'         => $lead,
		'heading_title2'        => '',
		'heading_alignment'     => 'left',
		'enable_link'           => 'no',
		'prefix_text_color'     => $C['heading'],
		'heading_color'         => $C['muted'],
		'heading_top_margin'    => $lead ? '14' : '0',
		'heading_bottom_margin' => '16',
		'prefix_typography'     => $typo( 11, 700, array( 'letter_spacing' => array( 'length' => '1.6' ), 'text_transform' => 'uppercase' ) ),
		'title_typography'      => $typo( 14, 400, array( 'line_height' => array( 'length' => '1.5' ) ) ),
		'margin_top'            => '0',
		// heading_bottom_margin only applies under a title; without one, space it here.
		'margin_bottom'         => $lead ? '0' : '14',
	) );
};

$list = function ( $id, $items ) use ( $set, $typo, $C ) {
	$set( $id, array(
		'list_type'       => 'icon',
		'list_icon'       => 'fas fa-paw',
		'list_items'      => $items,
		'item_margin'     => '12',
		'icon_space'      => '12',
		'icon_color'      => $C['icon'],
		'icon_size'       => '16',
		'text_color'      => $C['text'],
		'text_typography' => $typo( 14.5, 400, array( 'line_height' => array( 'length' => '1.45' ) ) ),
		'margin_top'      => '0',
		'margin_bottom'   => '0',
	) );
};

$button = function ( $id, $text ) use ( $set, $typo, $border, $C ) {
	$set( $id, array(
		'text'               => $text,
		'icon'               => '',
		'width'              => 'full',
		'align'              => 'center',
		'text_color'         => '#ffffff',
		'text_hover_color'   => '#ffffff',
		'bg_color'           => $C['cta'],
		'bg_hover_color'     => $C['ctaHov'],
		'border'             => $border( 0, $C['cta'], 12, array( 'color' => 'rgba(255,159,44,0.3)', 'horizontal' => '0', 'vertical' => '4', 'blur' => '12', 'spread' => '0' ) ),
		'border_hover_color' => $C['ctaHov'],
		'typography'         => $typo( 16, 700, array( 'text_align' => 'center', 'line_height' => array( 'length' => '1.2' ) ) ),
		'padding_top'        => '15',
		'padding_bottom'     => '15',
		'padding_left'       => '20',
		'padding_right'      => '20',
		'margin_top'         => '0',
		'margin_bottom'      => '0',
	) );
};

$card = function ( $outer, $class, $featured = false ) use ( $set, $border, $C ) {
	$set( $outer, array(
		'bg_color'      => '#ffffff',
		'class'         => $class,
		'place_content' => array( 'vertical' => '', 'horizontal' => '' ),
		'border'        => $featured
			? $border( 2, $C['accent'], 18, array( 'color' => 'rgba(15,56,51,0.12)', 'horizontal' => '0', 'vertical' => '18', 'blur' => '40', 'spread' => '0' ) )
			: $border( 1, $C['line'], 18, array( 'color' => 'rgba(15,56,51,0.05)', 'horizontal' => '0', 'vertical' => '2', 'blur' => '8', 'spread' => '0' ) ),
		'padding_top'    => '32',
		'padding_right'  => '32',
		'padding_bottom' => '32',
		'padding_left'   => '32',
		'padding_top_responsive'    => '26',
		'padding_right_responsive'  => '22',
		'padding_bottom_responsive' => '26',
		'padding_left_responsive'   => '22',
	) );
};

$inner_box = function ( $id ) use ( $set ) {
	$set( $id, array( 'gap' => '0', 'grid_gap_row' => '0', 'grid_gap_column' => '0' ) );
};

// ---------------------------------------------------------------- the row
$row = null;
foreach ( $data as $nid => $n ) {
	if ( 'row' === $n->type && 'dogco-pricing' === ( $n->settings->id ?? '' ) ) {
		$row = $nid;
	}
}
if ( ! $row ) {
	WP_CLI::error( 'Row #dogco-pricing not found' );
}

// Section header (column m6rdsbtowpl1).
$head_col = 'm6rdsbtowpl1';
$del( 'ye6a3ib17n4l' ); // DogCo logo above the title; the design uses an eyebrow instead.
$eyebrow = $new( 'rich-text', $head_col, array_merge( $margins0, array(
	'text'       => '<p>DogCo Member Pricing</p>',
	'color'      => $C['cta'],
	'class'      => 'dp-eyebrow',
	'typography' => $typo( 11, 700, array( 'text_align' => 'center', 'letter_spacing' => array( 'length' => '2' ), 'text_transform' => 'uppercase' ) ),
	'margin_bottom' => '16',
) ) );
$set( 't0h9gnkyxosm', array_merge( $margins0, array(
	'heading'    => 'Exclusive pricing for<br> DogCo Launch members.',
	'tag'        => 'h2',
	'color'      => $C['title'],
	'typography' => $typo( 44, 700, array( 'text_align' => 'center', 'line_height' => array( 'length' => '1.15' ), 'letter_spacing' => array( 'length' => '-0.4' ), 'text_transform' => 'capitalize' ) ),
	'typography_responsive' => array( 'font_size' => array( 'length' => '32', 'unit' => 'px' ) ),
	'margin_bottom' => '20',
) ) );
$set( '0jh1vd9uq87z', array_merge( $margins0, array(
	'text'       => '<p>Choose the hiring solution that fits your business and get an extra 10% off Discovered pricing as a DogCo Launch member.</p>',
	'color'      => $C['muted'],
	'class'      => 'dp-sub',
	'typography' => $typo( 18, 400, array( 'text_align' => 'center', 'line_height' => array( 'length' => '1.6' ) ) ),
	'typography_responsive' => array( 'font_size' => array( 'length' => '16', 'unit' => 'px' ) ),
	'margin_bottom' => '32',
) ) );
$set( 'umqr8wjxl0dt', array_merge( $margins0, array(
	'text'        => '<span class="dp-badge-label">DogCo Member Benefit</span><span class="dp-badge-sep" aria-hidden="true"></span>Extra 10% Off Discovered Pricing',
	'icon'        => 'fas fa-paw',
	'icon_position' => 'before',
	'class'       => 'dp-badge',
	'width'       => 'auto',
	'align'       => 'center',
	'text_color'  => '#ffffff',
	'bg_color'    => $C['cta'],
	'bg_hover_color' => $C['cta'],
	'border'      => $border( 0, $C['cta'], 999 ),
	'typography'  => $typo( 15, 700, array( 'line_height' => array( 'length' => '1.3' ) ) ),
	'padding_top' => '12', 'padding_bottom' => '12', 'padding_left' => '24', 'padding_right' => '24',
	'margin_bottom' => '56',
) ) );
$order( $head_col, array( $eyebrow, 't0h9gnkyxosm', '0jh1vd9uq87z', 'umqr8wjxl0dt', 'k2htybgdufcn' ) );
$data['k2htybgdufcn']->position = 4;

// Cards column (6v158hpwls9c): drop the old billing toggle above the cards.
$cards_col = '6v158hpwls9c';
$del( 'k6dfhv3wu7p1' );
$set( 'unmwjxzbvc4a', array( 'class' => 'dp-cards', 'grid_gap_row' => '24', 'grid_gap_column' => '24' ) );

// Essentials.
$card( '4imzj8d1hl6k', 'dp-card' );
$inner_box( 'hvzajoy7n2k5' );
$plan_head( '53syda7q0zh1' );
$set( '53syda7q0zh1', array( 'heading_title' => 'Essentials Plan' ) );
$set( 'ms3j7p5edqu2', array(
	'prefix_text'           => '',
	'heading_title'         => '$0',
	'dual_heading'          => 'no',
	'heading_title2'        => '',
	'heading_sub_title'     => '<p>Included at no cost</p>',
	'heading_alignment'     => 'left',
	'heading_color'         => $C['accent'],
	'sub_heading_color'     => $C['muted'],
	'heading_top_margin'    => '0',
	'heading_bottom_margin' => '0',
	'sub_heading_top_margin' => '6',
	'title_typography'      => $typo( 50, 700, array( 'line_height' => array( 'length' => '1' ), 'letter_spacing' => array( 'length' => '-1' ) ) ),
	'desc_typography'       => $typo( 14, 400, array( 'line_height' => array( 'length' => '1.5' ) ) ),
	'margin_top' => '0', 'margin_bottom' => '0', 'margin_top_responsive' => '0', 'margin_bottom_responsive' => '0',
) );
$del( 'vl7q849cn2f0' );
$set( 'w6us8rgzn1yb', array( 'color' => $C['line'], 'margin_top' => '24', 'margin_bottom' => '24' ) );
$features_head( 'yi0315pdo6hs' );
$list( '0i6wvx72zgnh', array(
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
) );
$button( 'hneuj1daisc6', 'Get Started' );
$ess = 'hvzajoy7n2k5';
$order( $ess, array( '53syda7q0zh1', $sep( $ess ), 'ms3j7p5edqu2', $sep( $ess ), 'hneuj1daisc6', 'w6us8rgzn1yb', 'yi0315pdo6hs', '0i6wvx72zgnh' ) );

// Unlimited.
$card( 'y5mzb792jtiq', 'dp-card dp-card--featured', true );
$inner_box( 'dklzqwgma0if' );
$plan_head( 'p4mo1fig0cej' );
$set( 'p4mo1fig0cej', array( 'heading_title' => 'Unlimited Plan', 'margin_bottom' => '22' ) );
foreach ( array( 'ypose4lm5j36', 'wu2tpzox3ceb', 'vqmtr7ogzn1c', '3kjfth8czy7l' ) as $old ) {
	$del( $old );
}
$calc = $new( 'html', 'dklzqwgma0if', array_merge( $margins0, array( 'html' => '[partner_pricing partner="dogco"]', 'margin_bottom' => '22' ) ) );
$button( 'y8zcn6euaqxb', 'Get Unlimited' );
$set( 'x0fwnbgv3p6a', array( 'color' => $C['line'], 'margin_top' => '26', 'margin_bottom' => '24' ) );
$features_head( 'hpxnsgjtmy5o', 'Everything in Essentials, plus:' );
$list( 'amzsbwiy1d0e', array(
	'Unlimited Pre-Employment Assessments & Predictive Analytics',
	'Market Openings Across All Major Job Boards',
	'Unlimited Assessments, Video Interviews & Reference Checking',
	'Automated Hiring Process',
	'Email & SMS Integration',
	'All 44+ Features Unlimited Use',
	'Dedicated Hiring Coach & Implementor',
) );
$order( 'dklzqwgma0if', array( 'p4mo1fig0cej', $calc, 'y8zcn6euaqxb', 'x0fwnbgv3p6a', 'hpxnsgjtmy5o', 'amzsbwiy1d0e' ) );

// IntegrityFirst: the price is plain rich-text so it can be edited in BB.
$card( 'qt17auhkocyp', 'dp-card' );
$inner_box( 'evr0jfpc9w8t' );
$plan_head( 'u8av4l7j5wi2', 'Pay Per Assessment' );
$set( 'u8av4l7j5wi2', array( 'heading_title' => 'IntegrityFirst' ) );
$del( 'ivy5u6wlbjck' );
$del( 'zcxqbds1n24a' );
$if_price = $new( 'rich-text', 'evr0jfpc9w8t', array_merge( $margins0, array(
	'class' => 'dp-price',
	'text'  => '<p class="dp-regular">Regularly <s>$14.75</s>/assessment</p>'
		. '<p class="dp-now"><span class="dp-amount">$13.28</span> <span class="dp-per">/assessment</span></p>'
		. '<p class="dp-member"><span class="dp-member-label">DogCo Member Price</span> <span class="dp-pill">Extra 10% Off</span></p>'
		. '<p class="dp-note">You save $1.47/assessment</p>',
) ) );
$button( 'dnhque1rg6x7', 'Get IntegrityFirst' );
$set( 'l75m4bed1ytn', array( 'color' => $C['line'], 'margin_top' => '24', 'margin_bottom' => '24' ) );
$set( 'daqoxjty9m17', array(
	'bg_color' => $C['tint'],
	'border'   => $border( 0, '', 12 ),
	'padding_top' => '13', 'padding_bottom' => '13', 'padding_left' => '16', 'padding_right' => '16',
	'margin_bottom' => '24',
) );
$set( '956b7togqrcd', array(
	'heading_color'         => $C['title'],
	'sub_heading_color'     => $C['muted'],
	'enable_link'           => 'no',
	'heading_top_margin'    => '0',
	'heading_bottom_margin' => '3',
	'title_typography'      => $typo( 16, 600, array( 'text_transform' => 'capitalize' ) ),
	'desc_typography'       => $typo( 12.5, 400, array( 'line_height' => array( 'length' => '1.45' ) ) ),
) );
$features_head( 'xza02qug39my' );
$list( 'vlq0pi3nsbm6', array(
	'10% off every assessment for DogCo members',
	'8–10 minute integrity assessment',
	'Available in 27+ languages',
	'Mobile-friendly hiring workflow',
	'Instant Qualified or Unqualified results',
	'Essentials features included',
) );
$ifc = 'evr0jfpc9w8t';
$order( $ifc, array( 'u8av4l7j5wi2', $sep( $ifc ), $if_price, $sep( $ifc ), 'dnhque1rg6x7', 'l75m4bed1ytn', 'daqoxjty9m17', 'xza02qug39my', 'vlq0pi3nsbm6' ) );

// Bottom callout, between the cards and "Need help choosing?".
$callout = $new( 'box', $cards_col, array(
	'layout'         => 'flex',
	'flex_direction' => 'row',
	'place_content'  => array( 'vertical' => 'start', 'horizontal' => '' ),
	'gap'            => '16',
	'class'          => 'dp-callout',
	'bg_type'        => 'basic',
	'bg_color'       => $C['tint'],
	'border'         => $border( 0, '', 18 ),
	'padding_top'    => '26', 'padding_bottom' => '26', 'padding_left' => '30', 'padding_right' => '30',
	'margin_top'     => '48', 'margin_bottom' => '40',
	'responsive_display' => 'desktop,large,medium,mobile',
	'container_element'  => 'div',
) );
$paw = $new( 'icon', $callout, array_merge( $margins0, array(
	'icon' => 'fas fa-paw', 'size' => '22', 'color' => $C['icon'], 'align' => 'left',
) ) );
$callout_text = $new( 'rich-text', $callout, array_merge( $margins0, array(
	'class' => 'dp-callout-text',
	'text'  => '<p><strong>More flexibility for DogCo members.</strong></p><p>DogCo Launch members receive an extra 10% off Discovered pricing, plus access to monthly billing options not normally available to companies of this size.</p>',
	'color' => $C['muted'],
	'typography' => $typo( 14.5, 400, array( 'line_height' => array( 'length' => '1.6' ) ) ),
) ) );
$order( $callout, array( $paw, $callout_text ) );
$order( $cards_col, array( 'unmwjxzbvc4a', $callout, 'iv8jxf2zkgw9', 'g5yd429lcrsm' ) );

// ---------------------------------------------------------------- page CSS
$css_block = <<<'CSS'
/* dogco-pricing: start (tools/dogco-layout.php) */
#dogco-pricing .dp-sub { max-width: 580px; margin-left: auto; margin-right: auto; }
#dogco-pricing .dp-badge .fl-button { box-shadow: 0 6px 18px rgba(255, 159, 44, .25); cursor: default; }
#dogco-pricing .dp-badge .fl-button i { font-size: 15px; }
#dogco-pricing .dp-badge-label { font-size: 11px; letter-spacing: .16em; text-transform: uppercase; opacity: .85; }
#dogco-pricing .dp-badge-sep { display: inline-block; width: 1px; height: 14px; margin: 0 12px; vertical-align: -2px; background: rgba(255, 255, 255, .45); }
#dogco-pricing .dp-cards { align-items: start; }
#dogco-pricing .dp-card--featured { position: relative; overflow: hidden; }
/* BB gives module ::before display:table (clearfix), which collapses it to 0 width. */
#dogco-pricing .dp-card--featured::before { content: ""; display: block; position: absolute; top: 0; left: 0; right: 0; width: 100%; height: 5px; background: #33b29c; z-index: 1; }
#dogco-pricing .dp-price p { margin: 0; }
#dogco-pricing .dp-regular { font-size: 14px; color: #8a959a; margin-bottom: 8px !important; }
#dogco-pricing .dp-now { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 4px 8px; margin-bottom: 12px !important; }
#dogco-pricing .dp-amount { font-size: clamp(38px, 3.5vw, 50px); font-weight: 700; line-height: 1; letter-spacing: -.02em; color: #33b29c; }
#dogco-pricing .dp-per { font-size: 16px; color: #4a5759; padding-bottom: 5px; }
#dogco-pricing .dp-member { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 8px; margin-bottom: 8px !important; }
#dogco-pricing .dp-member-label { font-size: 11px; font-weight: 700; letter-spacing: clamp(.08em, .9vw, .16em); text-transform: uppercase; color: #4a5759; }
#dogco-pricing .dp-pill { padding: 3px 10px; border-radius: 999px; background: #ff9f2c; color: #fff; font-size: 11px; font-weight: 700; white-space: nowrap; }
#dogco-pricing .dp-note { font-size: 14px; line-height: 1.5; color: #4a5759; }
#dogco-pricing .dp-callout { max-width: 680px; margin-left: auto !important; margin-right: auto !important; }
#dogco-pricing .dp-callout-text p { margin: 0; }
#dogco-pricing .dp-callout-text strong { display: block; margin-bottom: 4px; font-size: 17px; color: #33b29c; }
@media (max-width: 991px) {
	#dogco-pricing .dp-cards { grid-template-columns: minmax(0, 1fr) !important; max-width: 520px; margin: 0 auto; }
	#dogco-pricing .dp-card--featured { order: -1; }
}
@media (max-width: 480px) {
	#dogco-pricing .dp-badge .fl-button { border-radius: 22px !important; padding: 10px 18px !important; }
	#dogco-pricing .dp-badge-sep { display: none; }
	#dogco-pricing .dp-badge-label { display: block; margin-bottom: 2px; }
}
/* dogco-pricing: end */
CSS;

$dset = $dset ?: new stdClass();
$css  = (string) ( $dset->css ?? '' );
$css  = preg_replace( '#\n?/\* dogco-pricing: start.*?/\* dogco-pricing: end \*/\n?#s', "\n", $css );
$dset->css = rtrim( $css ) . "\n\n" . $css_block . "\n";

update_post_meta( $page, '_fl_builder_data', $data );
update_post_meta( $page, '_fl_builder_draft', $data );
update_post_meta( $page, '_fl_builder_data_settings', $dset );
update_post_meta( $page, '_fl_builder_draft_settings', $dset );
FLBuilderModel::delete_asset_cache_for_all_posts();

WP_CLI::success( "Page $page: #dogco-pricing restyled; calculator module $calc" );
