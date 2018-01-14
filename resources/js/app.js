var Sonoff;
$( document ).on( "ready", function () {
	/**
	 * Sonoff Handler
	 * @type {Sonoff}
	 */
	Sonoff = new Sonoff( { timeout: 5 } );
	
	
	var appendLoading = function ( elem, replace ) {
		var replace = replace || false;
		var loader  = $( '<div>', { class: "loader" } ).append( $( 'img', { src: "/resources/img/loading.gif" } ) );
		
		if ( replace ) {
			$( elem ).html( loader );
		} else {
			$( elem ).append( loader );
		}
	};
	
	$( '.hamburger' ).click( function () {
		$( "#navi" ).toggleClass( "show" );
		$( '.hamburger' ).toggleClass( "open" );
	} );
	
	
} );


function notifyMe( msg, title ) {
	var title = title || "";
	if ( title != "" ) {
		title = " - " + title;
	}
	
	var icon = "/resources/img/favicons/apple-icon-180x180.png";
	
	// Let's check if the browser supports notifications
	if ( !(
			"Notification" in window
		) ) {
		return;
	}
	
	
	// Let's check whether notification permissions have already been granted
	else if ( Notification.permission === "granted" ) {
		// If it's okay let's create a notification
		var notification = new Notification( "SonWEB" + title, { body: msg, icon: icon } );
		setTimeout( notification.close.bind( notification ), 3000 );
	}
	
	// Otherwise, we need to ask the user for permission
	else if ( Notification.permission !== 'denied' ) {
		Notification.requestPermission( function ( permission ) {
			// If the user accepts, let's create a notification
			if ( permission === "granted" ) {
				var notification = new Notification( "SonWEB" + title, { body: msg } );
				setTimeout( notification.close.bind( notification ), 3000 );
			}
		} );
	}
	
	// Finally, if the user has denied notifications and you
	// want to be respectful there is no need to bother them any more.
}
