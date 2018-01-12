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
} );
