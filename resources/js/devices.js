$( document ).on( "ready", function () {
	deviceTools();
	updateStatus();
} );


function updateStatus() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "get status for " + $( tr ).data( "device_ip" ) );
		var device_ip = $( tr ).data( "device_ip" );
		
		Sonoff.getStatus( device_ip, null, function ( data ) {
			
			if ( data ) {
				$( tr ).find( ".status" ).html( data.POWER );
			} else {
				$( tr ).find( ".status" ).html( "Fehler" );
			}
			//console.log( result );
			
		} );
	} );
	
	
	setTimeout( function () {
		//updateStatus();
	}, 1000 );
	
};

function deviceTools() {
	$( '#device-list tbody tr td.status' ).on( "click", function ( e ) {
		e.preventDefault();
		var statusField = $( this );
		var device_ip   = $( this ).closest( "tr" ).data( "device_ip" );
		Sonoff.toggle( device_ip, 1, function ( data ) {
			if ( data.POWER ) {
				statusField.html( data.POWER );
			} else {
				statusField.html( "Fehler" );
			}
		} );
		
		
	} );
}