<?php
/*
Plugin Name:	Fury
Description:	GDPR-compliant tool set to disable or re-enable tracking.
Version:		0.15.2
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
	// don’t do anything if site is not tracking
	if ( ! rh_fury_site_is_tracking() ) return;
	// don’t add javascript if the gdpr cookie is set to true
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr']  ) :
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
	
	// get privacy link
	$privacy_link = get_option( 'rh_fury_privacy_link' );
	
	if ( $privacy_link === false ) {
		add_option( 'rh_fury_privacy_link', '/datenschutz/' );
		
		$privacy_link = get_option( 'rh_fury_privacy_link' );
	}
	?>
<style><?php echo str_replace( '/*# sourceMappingURL=style.min.css.map */', '', $stylesheet ); ?></style>

<div id="gdpr-notice" class="gdpr-notice">
	<div class="container wrapper">
		<div class="notice-content">
			<p><?php _e( 'In order to be able to offer you the best possible user experience on this website in the future, we would like to activate tracking services such as Google Analytics, which uses cookies to anonymously store and analyse your user behaviour. For this, we need your consent, which you can revoke at any time.', 'rh-fury' ); ?><br>
			<?php printf( __( 'For more information about the services used, please, see our %s.', 'rh-fury' ), '<a href="' . $privacy_link . '" class="datenschutz-open-close">' . __( 'privacy policy', 'rh-fury' ) . '</a>' ); ?></p>
		</div>
		
		<div class="notice-buttons">
			<a id="gdpr-yes" class="btn btn-primary"><?php _e( 'Allow', 'rh-fury' ); ?></a>
			<a id="gdpr-no" class="gdpr-no-button"><?php _e( 'Prohibit', 'rh-fury' ); ?></a>
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
	
	echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $options . ( $disabled ? '-->' : '' ) . '</div>';
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
		
		echo '<div id="rh-fury-tracking">' . ( $disabled ? '<!--' : '' ) . $option . ( $disabled ? '-->' : '' ) . '</div>';
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
	if ( ! isset( $_COOKIE['mws-gdpr'] ) || ! $_COOKIE['mws-gdpr'] ) {
		$disabled = true;
	}
	
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
