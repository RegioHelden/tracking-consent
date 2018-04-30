/**
 * Functions for the GDPR compliance notice.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	var gdpr_yes = document.getElementById( 'gdpr-yes' );
	var gdpr_no = document.getElementById( 'gdpr-no' );
	var notice = document.getElementById( 'gdpr-notice' );
	
	set_height();
	
	window.onresize = function() {
		set_height();
	}
	
	gdpr_yes.addEventListener( 'click', function( event ) {
		set_cookie( 'mws-gdpr', true, 30 );
		document.body.removeAttribute( 'style' );
		notice.remove();
	} );
	
	gdpr_no.addEventListener( 'click', function( event ) {
		set_cookie( 'mws-gdpr', false, 1 );
		document.body.removeAttribute( 'style' );
		notice.remove();
	} );
	
	/**
	 * Set a cookie.
	 * 
	 * @param	{String}		name
	 * @param	{String}		value
	 * @param	{Number}		days
	 */
	function set_cookie( name, value, days ) {
		var expires = '';
		if ( days ) {
			var date = new Date();
			
			date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
			expires = '; expires=' + date.toUTCString();
		}
		
		document.cookie = name + '=' + ( value || '' ) + expires + '; path=/';
	}
	
	/**
	 * Calculate info notice height
	 */
	function set_height() {
		if ( notice ) {
			var height = notice.offsetHeight;
			var is_mobile = ! window.matchMedia( '(min-width: 840px)' ).matches;
			
			document.body.removeAttribute( 'style' );
			
			if ( is_mobile ) {
				document.body.style.paddingBottom = height + 'px';
			}
			else {
				document.body.style.paddingTop = height + 'px';
			}
		}
	}
} );