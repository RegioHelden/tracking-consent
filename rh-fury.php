<?php
/*
Plugin Name:	Fury
Description:	GDPR-compliant tool set to disable or re-enable tracking.
Version:		0.3.0
Author:			Matthias Kittsteiner
License:		GPL3
License URI:	https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:	rh-fury
Domain Path:	/languages

Fury is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.
 
Fury is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Fury. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Load text domain.
 */
function rh_fury_load_textdomain() {
	load_plugin_textdomain( 'rh-fury', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'rh_fury_load_textdomain' );


/**
 * Add the “cookie” information notice.
 */
function rh_fury_add_info_notice() {
	// don’t add javascript if the gdpr cookie is set to true
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr'] ) :
	// add javascript
	$javascript = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/js/gdpr-notice.min.js' );
	?>
<script><?php echo str_replace( '//# sourceMappingURL=gdpr-notice.min.js.map', '', $javascript ); ?></script>
	<?php
	endif;
	
	// check for cookie
	if ( isset( $_COOKIE['mws-gdpr'] ) ) return;
	
	// add stylesheet
	$stylesheet = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/style/style.min.css' );
	?>
<style><?php echo str_replace( '/*# sourceMappingURL=style.min.css.map */', '', $stylesheet ); ?></style>

<div id="gdpr-notice" class="gdpr-notice">
	<div class="container">
		<div class="notice-content">
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. <br>
			Mehr Informationen erhalten Sie in unserer <a href="/impressum/">Datenschutzerklärung</a>.</p>
		</div>
		
		<div class="notice-buttons">
			<button id="gdpr-yes">Erlauben</button>
			<button id="gdpr-no" class="gdpr-no-button">Verbieten</button>
		</div>
	</div>
</div>
	<?php
}

// disable for WD50 until we have a valid notice text
if ( defined( 'RH_CONFIG' ) && RH_CONFIG['project'] != 'wd50' ) {
	add_action( 'wp_footer', 'rh_fury_add_info_notice' );
}


// only on zephyr projects
// see: https://docs.aurora.ci/handbook/environment-variable.html
if ( defined( 'RH_CONFIG' ) && RH_CONFIG['version'] === 'zephyr' ) :
/**
 * Customizer settings.
 * 
 * @param	WP_Customize_Manager		$wp_customize Theme Customizer object
 */
function rh_fury_customizer_register( $wp_customize ) {
	$wp_customize->add_setting( 'tracking_js_textarea', [
		'default' => '',
		'type' => 'option',
		'capability' => 'edit_theme_options',
		'transport' => '',
	] );
	
	$wp_customize->add_control( 'tracking_js_textarea', [
		'type' => 'code_editor',
		'setting' => 'js',
		'input_attrs' => [
			'class' => 'code',
			// Ensures contents displayed as LTR instead of RTL.
		],
		'priority' => 10,
		'section' => 'seo_analytics',
		'label' => __( 'Tracking JavaScript code', 'rh-fury' ),
		'description' => __( 'Don’t forget to add a beginning &lt;script&gt; and an ending &lt;/script&gt;.', 'rh-fury' ),
	] );
}

add_action( 'customize_register', 'rh_fury_customizer_register', 20 );


/**
 * Add the tracking code to the footer on Zephyr projects.
 */
function rh_fury_zephyr_tracking_code() {
	$disabled = rh_fury_check_gdpr_cookie();
	
	if ( get_option( 'tracking_js_textarea' ) ) {
		// remove every html comment
		$option = preg_replace( '/<!--(.*?)-->/', '', get_option( 'tracking_js_textarea' ) );
		
		echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $option . ( $disabled ? '-->' : '' ) . '</div>';
	}
}

add_action( 'wp_footer', 'rh_fury_zephyr_tracking_code' );
endif;

// only on aster projects
// see: https://docs.aurora.ci/handbook/environment-variable.html
if ( defined( 'RH_CONFIG' ) && RH_CONFIG['version'] === 'aster' ) :
/**
 * Add the tracking code to the footer on Aster projects.
 */
function rh_fury_aster_tracking_code() {
	$disabled = rh_fury_check_gdpr_cookie();
	
	if ( ! empty( $tocki_redux_themeoptions["tocki_redux_footer"] ) ) {
		// remove every html comment
		$option = preg_replace( '/<!--(.*?)-->/', '', $tocki_redux_themeoptions["tocki_redux_footer"] );
		// set theme options "empty" to avoid our default tracking 
		$tocki_redux_themeoptions["tocki_redux_footer"] = '<script></script>';
		
		echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $option . ( $disabled ? '-->' : '' ) . '</div>';
	}
	else {
		// set theme options "empty" to avoid our default tracking
		$tocki_redux_themeoptions["tocki_redux_footer"] = '<script></script>';
	}
}

add_action( 'wp_footer', 'rh_fury_aster_tracking_code' );
endif;


/**
 * Check if a GDPR cookie is set.
 * 
 * @return		boolean
 */
function rh_fury_check_gdpr_cookie() {
	$disabled = false;
	
	// disable if tracking nor allowed neither already asked for
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr'] ) {
		$disabled = true;
	}
	
	// disable for webdesign.extern/wd50 until we have a valid notice text
	if ( defined( 'RH_CONFIG' ) && ( RH_CONFIG['project'] == 'webdesign.extern' || RH_CONFIG['project'] == 'wd50' ) ) {
		$disabled = false;
	}
	
	return $disabled;
}