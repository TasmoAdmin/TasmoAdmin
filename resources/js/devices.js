$( document ).on( "ready", function () {
	deviceTools();
	updateStatus();
} );


function updateStatus() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "get status for " + $( tr ).data( "device_ip" ) );
		var device_ip     = $( tr ).data( "device_ip" );
		var device_relais = $( tr ).data( "device_relais" );
		if ( !$( tr ).hasClass( "updating" ) ) {
			$( tr ).addClass( "updating" );
			
			Sonoff.getStatus( device_ip, device_relais, function ( data ) {
				$( tr ).removeClass( "updating" );
				if ( data ) {
					var device_status = data.POWER || eval( "data.POWER" + device_relais );
					$( tr ).find( ".status" ).html( (
						                                device_status == "ON" ? "AN" : "AUS"
					                                ) );
				} else {
					$( tr ).find( ".status" ).html( "Fehler" );
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
	$( '#device-list tbody tr td.status' ).on( "click", function ( e ) {
		e.preventDefault();
		var statusField   = $( this );
		var device_ip     = $( this ).closest( "tr" ).data( "device_ip" );
		var device_relais = $( this ).closest( "tr" ).data( "device_relais" );
		Sonoff.toggle( device_ip, device_relais, function ( data ) {
			if ( data ) {
				var device_status = data.POWER || eval( "data.POWER" + device_relais );
				statusField.html( (
					                  device_status == "ON" ? "AN" : "AUS"
				                  ) );
			} else {
				statusField.html( "Fehler" );
			}
		} );
		
		
	} );
}