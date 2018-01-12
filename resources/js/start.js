$( document ).on( "ready", function () {
	deviceTools();
	updateStatus();
} );


function updateStatus() {
	$( '#content .box_device' ).each( function ( key, elem ) {
		
		console.log( "get status for " + $( elem ).data( "device_ip" ) );
		var device_ip     = $( elem ).data( "device_ip" );
		var device_relais = $( elem ).data( "device_relais" );
		if ( !$( elem ).hasClass( "updating" ) ) {
			$( elem ).addClass( "updating" );
			Sonoff.getStatus( device_ip, device_relais, function ( data ) {
				$( elem ).removeClass( "updating" );
				console.log( data );
				if ( data ) {
					var img           = $( elem ).find( "img" );
					var src           = "/resources/img/device_icons/" + img.data( "icon" ) + "_%pw.png";
					var device_status = data.POWER || eval( "data.POWER" + device_relais );
					src               = src.replace( "%pw", device_status.toLowerCase() );
					img.attr( "src", src ).parent().removeClass( "animated" );
					$( elem ).removeClass( "error" ).find( ".animated" ).removeClass( "animated" );
				} else {
					$( elem ).addClass( "error" ).find( ".animated" ).removeClass( "animated" );
				}
				//console.log( result );
				
			} );
		}
	} );
	
	
	setTimeout( function () {
		updateStatus();
	}, 5000 );
	
};

function deviceTools() {
	$( '#content .box_device' ).on( "click", function ( e ) {
		e.preventDefault();
		var device_box = $( this );
		device_box.find( "img" ).effect( "shake", { distance: 3 } );
		var device_ip     = device_box.data( "device_ip" );
		var device_relais = device_box.data( "device_relais" );
		Sonoff.toggle( device_ip, device_relais, function ( data ) {
			if ( data ) {
				var img           = device_box.find( "img" );
				var src           = "/resources/img/device_icons/" + img.data( "icon" ) + "_%pw.png";
				var device_status = data.POWER || eval( "data.POWER" + device_relais );
				src               = src.replace( "%pw", device_status );
				img.attr( "src", src ).parent().removeClass( "animated" );
				device_box.removeClass( "error" );
			} else {
				device_box.addClass( "error" );
			}
		} );
		
		
	} );
}