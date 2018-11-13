<?php
/*
Plugin Name:	Fury
Description:	GDPR-compliant tool set to disable or re-enable tracking.
Version:		0.23
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
 * Add the JavaScript and CSS to the head.
 */
function rh_fury_add_assets() {
	// don’t do anything if site is not tracking
	if ( ! rh_fury_site_is_tracking() || rh_fury_disable() ) return;
	// don’t add javascript if the gdpr cookie is set to true
	// phpcs:disable WordPress.VIP.ValidatedSanitizedInput.MissingUnslash, WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr'] ) :
	// phpcs:enable
	// add javascript
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents
	$javascript = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/js/gdpr-notice.min.js' );
	// phpcs:enable
	// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
	?>
	<script><?php echo trim( str_replace( '//# sourceMappingURL=gdpr-notice.min.js.map', '', $javascript ) ); ?></script>
	<?php
	// phpcs:enable
	endif;
	
	// check for cookie
	if ( isset( $_COOKIE['mws-gdpr'] ) ) return;
	
	// add stylesheet
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents
	$stylesheet = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/style/style.min.css' );
	// phpcs:enable
	
	// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
	?>
<style><?php echo trim( str_replace( '/*# sourceMappingURL=style.min.css.map */', '', $stylesheet ) ); ?></style>
	<?php
	// phpcs:enable
}

add_action( 'wp_head', 'rh_fury_add_assets', 100 );

/**
 * Add the “cookie” information notice.
 */
function rh_fury_add_info_notice() {
	// don’t do anything if site is not tracking
	if ( ! rh_fury_site_is_tracking() || rh_fury_disable() ) return;
	// check for cookie
	if ( isset( $_COOKIE['mws-gdpr'] ) ) return;
	
	// get privacy link
	$privacy_link = get_option( 'tracking_consent_privacy_link' );
	
	if ( $privacy_link === false && defined( 'RH_CONFIG' ) ) {
		add_option( 'tracking_consent_privacy_link', '/datenschutz/' );
		
		$privacy_link = get_option( 'tracking_consent_privacy_link' );
	}
	else if ( $privacy_link === false && ! defined( 'RH_CONFIG' ) ) {
		$privacy_link = get_privacy_policy_url();
	}
	?>
<div id="gdpr-notice" class="gdpr-notice<?php echo ( wp_is_mobile() ? ' gdpr-mobile' : ' gdpr-desktop' ) . ( ! get_option( 'rh_fury_design_old' ) ? ' fullscreen' : '' ); ?>">
	<div class="container wrapper">
		<div class="notice-content">
			<p><?php esc_html_e( 'In order to be able to offer you the best possible user experience on this website in the future, we would like to activate tracking services such as Google Analytics, which uses cookies to anonymously store and analyse your user behaviour. For this, we need your consent, which you can revoke at any time.', 'rh-fury' ); ?><br>
			<?php
			/* translators: %s: link to privacy policy */
			printf( esc_html__( 'For more information about the services used, please, see our %s.', 'rh-fury' ), '<a href="' . esc_attr( $privacy_link ) . '" class="datenschutz-open-close">' . esc_html__( 'privacy policy', 'rh-fury' ) . '</a>' );
			?></p>
		</div>
		<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
		
		<?php // phpcs:enable ?>
		<div class="notice-buttons">
			<a id="gdpr-yes" class="btn btn-primary"><?php esc_html_e( 'Allow', 'rh-fury' ); ?></a>
			<a id="gdpr-no" class="gdpr-no-button"><?php esc_html_e( 'Prohibit', 'rh-fury' ); ?></a>
		</div>
	</div>
</div>
	<?php
}

add_action( 'wp_footer', 'rh_fury_add_info_notice' );


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
	$options = '';
	
	if ( get_option( 'tracking_js_textarea' ) ) {
		// remove every html comment
		$options .= preg_replace( '/<!--((.|\n)*?)-->/', '', get_option( 'tracking_js_textarea' ) );
	}
	
	if ( get_option( 'rh_analytics_id' ) && ! get_theme_mod( 'rh_analytics_disable' ) ) {
		$options .= "
<script>
	// disable tracking if the opt-out cookie exists.
	var disableStr = 'ga-disable-" . get_option( 'rh_analytics_id' ) . "';
	if (document.cookie.indexOf(disableStr + '=true') > -1) {
		window[disableStr] = true;
	}
	
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
	ga('create', '" . get_option( 'rh_analytics_id' ) . "', 'auto');
	ga('create', 'UA-63619645-1', 'auto', 'clientTracker');
	ga('send', 'pageview');
	ga('set', 'anonymizeIp', true);
	ga('clientTracker.send', 'pageview');
	" . do_action( 'rh_analytics_after_include' ) . "
</script>
";
	}
	
	// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
	echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $options . ( $disabled ? '-->' : '' ) . '</div>';
	// phpcs:enable
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
	global $tocki_redux_themeoptions;
	
	$disabled = rh_fury_check_gdpr_cookie();
	
	if ( rh_fury_site_is_tracking() ) {
		// remove every html comment
		$option = preg_replace( '/<!--((.|\n)*?)-->/', '', $tocki_redux_themeoptions['tocki_redux_footer'] );
		// set theme options "empty" to avoid our default tracking 
		$tocki_redux_themeoptions['tocki_redux_footer'] = '<script></script>';
		
		// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $option . ( $disabled ? '-->' : '' ) . '</div>';
		// phpcs:enable
	}
	else {
		// set theme options "empty" to avoid our default tracking
		$tocki_redux_themeoptions['tocki_redux_footer'] = '<script></script>';
		echo '<div id="rh-fury-tracking"></div>';
	}
}

add_action( 'wp_footer', 'rh_fury_aster_tracking_code' );
endif;


/**
 * Check if a GDPR cookie is set.
 * 
 * @return		bool
 */
function rh_fury_check_gdpr_cookie() {
	$disabled = false;
	
	// disable if tracking nor allowed neither already asked for
	// phpcs:disable WordPress.VIP.ValidatedSanitizedInput.MissingUnslash, WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr'] ) {
		$disabled = true;
	}
	// phpcs:enable
	
	return $disabled;
}

/**
 * Check if a website has enabled tracking scripts.
 * 
 * @return		bool
 */
function rh_fury_site_is_tracking() {
	global $tocki_redux_themeoptions;
	
	$is_tracking = true;
	
	if ( defined( 'RH_CONFIG' ) && RH_CONFIG['version'] === 'zephyr' ) {
		// get all options that enable or disable tracking
		$analytics_disabled = get_theme_mod( 'rh_analytics_disable' );
		$analytics_id = get_option( 'rh_analytics_id' );
		$tracking_js = get_option( 'tracking_js_textarea' );
		
		if ( $tracking_js || ( $analytics_id && ! $analytics_disabled ) ) {
			$is_tracking = true;
		}
		else {
			$is_tracking = false;
		}
	}
	else if ( defined( 'RH_CONFIG' ) && RH_CONFIG['version'] === 'aster' ) {
		$is_tracking = (bool) ! empty( $tocki_redux_themeoptions['tocki_redux_footer'] );
	}
	
	return $is_tracking;
}

/**
 * Detect tracking scripts in the tracking JavaScript.
 */
function rh_fury_detect_tracking_scripts() {
	$option = [];
	$script_content = get_option( 'tracking_js_textarea' );
	
	// Google Analytics
	if (
		strpos( $script_content, 'analytics' ) !== false
		|| get_option( 'rh_analytics_id' )
	) {
		$option[] = 'google-analytics';
	}
	
	// Google Tag Manager
	if (
		strpos( $script_content, 'gtag' ) !== false
		|| strpos( $script_content, 'googletagmanager' )
	) {
		$option[] = 'google-tag-manager';
	}
	
	// Google Remarketing
	if (
		strpos( $script_content, 'gtag(\'config\', \'AW-' ) !== false
		|| strpos( $script_content, 'google_conversion_id' ) !== false
		|| strpos( $script_content, 'google_remarketing' ) !== false
		|| strpos( $script_content, 'goog_report_conversion' ) !== false
		|| strpos( $script_content, 'conversion_async' ) !== false
	) {
		$option[] = 'google-remarketing';
	}
	
	// Google Click Identifier
	if ( strpos( $script_content, 'gclid' ) !== false ) {
		$option[] = 'google-click-identifier';
	}
	
	// Facebook Pixel
	if ( strpos( $script_content, 'connect.facebook.net/en_US/fbevents.js' ) !== false ) {
		$option[] = 'facebook-pixel';
	}
	
	// Bing Tracking
	if ( strpos( $script_content, 'bat.bing.com' ) !== false ) {
		$option[] = 'bing-tracking';
	}
	
	update_option( 'rh_fury_tracking_scripts', $option );
}

add_action( 'customize_save_after', 'rh_fury_detect_tracking_scripts' );

/**
 * Check if Fury should be disabled.
 * 
 * @return	bool
 */
function rh_fury_disable() {
	// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( isset( $_GET['rh_fury_disable'] ) && $_GET['rh_fury_disable'] === 'true' ) {
		return true;
	}
	// phpcs:enable
	
	return false;
}
