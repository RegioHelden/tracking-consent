/**
 * Functions for the GDPR compliance notice.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	var gdpr_yes = document.getElementById( 'gdpr-yes' );
	var gdpr_no = document.getElementById( 'gdpr-no' );
	var notice = document.getElementById( 'gdpr-notice' );
	
	// initialize on page load
	set_height();
	toggle_conversion_codes();
	
	window.onresize = function() {
		set_height();
	}
	
	// click on gdpr yes button
	if ( gdpr_yes ) {
		gdpr_yes.addEventListener( 'click', function( event ) {
			set_cookie( 'mws-gdpr', true, 30 );
			document.body.removeAttribute( 'style' );
			notice.remove();
			toggle_conversion_codes();
			
			// check if cookie is really set
			if ( get_cookie( 'mws-gdpr' ) === 'true' ) {
				var rh_fury_tracking = document.getElementById( 'rh-fury-tracking' );
				var rh_raw_html_tracking = document.querySelectorAll( '.rh-conversion-code' );
				
				// remove every html comment from the tracking div
				rh_fury_tracking.innerHTML = rh_fury_tracking.innerHTML.replace( /<!--/, '' ).replace( /-->/, '' );
				
				for ( var i = 0; i < rh_raw_html_tracking.length; i++ ) {
					rh_raw_html_tracking[ i ].innerHTML = rh_raw_html_tracking[ i ].innerHTML.replace( /<!--/, '' ).replace( /-->/, '' );
				}
				
				// get all script tags inside the tracking div
				var script_tags = document.querySelectorAll( '#rh-fury-tracking script, .rh-conversion-code script' );
				
				// insert every script tag inside the tracking div as a new
				// script to execute it
				for ( var i = 0; i < script_tags.length; i++ ) {
					var element = document.createElement( 'script' );
					
					if ( script_tags[ i ].src ) {
						// if script tag has a src attribute
						element.src = script_tags[ i ].src;
					}
					else {
						// if script tag has content
						element.innerHTML = script_tags[ i ].innerHTML;
					}
					
					// append it to body
					document.body.appendChild( element );
				}
				
				// remove tracking element because we don’t need it anymore
				rh_fury_tracking.remove();
				
				for ( var i = 0; i < rh_raw_html_tracking.length; i++ ) {
					rh_raw_html_tracking[ i ].remove();
				}
			}
		} );
	}
	
	// click on gdpr no button
	if ( gdpr_no ) {
		gdpr_no.addEventListener( 'click', function( event ) {
			set_cookie( 'mws-gdpr', false, 1 );
			document.body.removeAttribute( 'style' );
			notice.remove();
			toggle_conversion_codes();
		} );
	}
	
	/**
	 * Get a cookie by its name.
	 * 
	 * @see		https://stackoverflow.com/a/24103596/3461955
	 * 
	 * @param	{String}		name
	 * @return	{*}
	 */
	function get_cookie( name ) {
		var nameEQ = name + '=';
		var ca = document.cookie.split( ';' );
		
		for ( var i = 0; i < ca.length; i++ ) {
			var c = ca[ i ];
			while ( c.charAt( 0 ) == ' ' ) c = c.substring( 1, c.length );
			if ( c.indexOf( nameEQ ) == 0 ) return c.substring( nameEQ.length, c.length );
		}
		
		return null;
	}
	
	/**
	 * Set a cookie.
	 * 
	 * @see		https://stackoverflow.com/a/24103596/3461955
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
	
	/**
	 * Enable or disable Google conversion codes.
	 */
	function toggle_conversion_codes() {
		var conversion_elements = document.querySelectorAll( '[onclick]' );
		
		if ( ! conversion_elements.length ) {
			conversion_elements = document.querySelectorAll( '[data-onclick]' );
		}
		
		for ( var i = 0; i < conversion_elements.length; i++ ) {
			var element = conversion_elements[ i ];
			
			if ( get_cookie( 'mws-gdpr' ) === 'true' ) {
				// set href for link elements
				if ( element.tagName === 'A' || element.tagName === 'BUTTON' ) {
					// don’t resort
					var onclick = element.getAttribute( 'data-onclick' );
					var regex = /\('([^'])+/g;
					var result_array = regex.exec( onclick );
					console.log(result_array);
					var link = result_array[ 0 ].replace( '(\'', '' );
					
					element.href = link;
				}
				
				// set our previously stored data-onclick attribute
				element.setAttribute( 'onclick', element.getAttribute( 'data-onclick' ) );
				element.removeAttribute( 'data-onclick' );
			}
			else {
				var onclick = element.getAttribute( 'onclick' );
				
				// store onclick attribute in separate data attribute
				element.setAttribute( 'data-onclick', onclick );
				
				if ( element.tagName === 'A' || element.tagName === 'BUTTON' ) {
					// don’t resort
					var regex = /\('([^'])+/g;
					var result_array = regex.exec( onclick );
					var link = result_array[ 0 ].replace( '(\'', '' );
					
					// set the real link
					element.setAttribute( 'href', link );
					// remove onclick
					element.removeAttribute( 'onclick' );
				}
				else {
					// set an onclick to open something on non-anchor elements
					element.setAttribute( 'onclick', 'location.href=' + link );
				}
			}
		}
	}
} );