<?php
/*
Plugin Name:	Fury
Description:	GDPR-compliant tool set to disable or re-enable tracking.
Version:		0.1
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

// load text domain
load_plugin_textdomain( 'rh-fury', false, plugin_dir_path( __FILE__ ) . 'languages' );

/**
 * Add the “cookie” information notice.
 */
function rh_fury_add_info_notice() {
	// check for cookie
	if ( isset( $_COOKIE['mws-gdpr'] ) ) return;
	
	// add stylesheet
	$stylesheet = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/style/style.min.css' );
	// add javascript
	$javascript = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/js/gdpr-notice.min.js' );
	?>
<style><?php echo $stylesheet; ?></style>
<script><?php echo $javascript; ?></script>

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

add_action( 'wp_footer', 'rh_fury_add_info_notice' );