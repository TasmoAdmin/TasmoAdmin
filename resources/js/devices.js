$( document ).on( "ready", function () {
	deviceTools();
	updateStatus();
} );


function updateStatus() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "[Devices][updateStatus]get status from " + $( tr ).data( "device_ip" ) );
		var device_ip     = $( tr ).data( "device_ip" );
		var device_relais = $( tr ).data( "device_relais" );
		var device_group  = $( tr ).data( "device_group" );
		if ( !$( tr ).hasClass( "updating" ) ) {
			$( tr ).addClass( "updating" );
			
			if ( device_group == "multi" && device_relais > 1 ) {
				console.log( "[Devices][updateStatus]skip multi " + $( tr ).data( "device_ip" ) );
				return; //relais 1 will update all others
			}
			
			Sonoff.getStatus( device_ip, device_relais, function ( data ) {
				if ( data ) {
					if ( device_group == "multi" ) {
						$( '#device-list tbody tr[data-device_group="multi"][data-device_ip="' + device_ip + '"]' )
							.each( function ( key, grouptr ) {
								var device_status = eval( "data.StatusSTS.POWER" + $( grouptr )
									.data( "device_relais" ) );
								
								updateRow( $( grouptr ), data, device_status );
								$( grouptr ).removeClass( "updating" );
							} );
					} else {
						var device_status = data.StatusSTS.POWER || eval( "data.StatusSTS.POWER" + device_relais );
						
						updateRow( $( tr ), data, device_status );
					}
				} else {
					if ( device_group == "multi" ) {
						$( '#device-list tbody tr[data-device_group="multi"][data-device_ip="' + device_ip + '"]' )
							.each( function ( key, grouptr ) {
								
								$( grouptr ).find( ".status" ).html( "Fehler" );
								$( grouptr ).find( ".rssi" ).html( "Fehler" );
								$( grouptr ).find( ".runtime" ).html( "Fehler" );
								$( grouptr ).find( ".version" ).html( "Fehler" );
								$( grouptr ).removeClass( "updating" );
							} );
					} else {
						$( tr ).find( ".status" ).html( "Fehler" );
						$( tr ).find( ".rssi" ).html( "Fehler" );
						$( tr ).find( ".runtime" ).html( "Fehler" );
						$( tr ).find( ".version" ).html( "Fehler" );
						$( tr ).removeClass( "updating" );
					}
				}
				
			} );
		}
	} );
	
	if ( refreshtime ) {
		console.log( "[Global][Refreshtime]" + refreshtime + "ms" );
		setTimeout( function () {
			updateStatus();
		}, refreshtime );
	} else {
		console.log( "[Global][Refreshtime]Dont refresh" );
	}
	
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
					                  device_status == "ON" ? "AN" : (
						                  device_status == "OFF" ? "AUS" : device_status
					                  )
				                  ) );
			} else {
				statusField.html( "Fehler" );
			}
		} );
		
		
	} );
}

function updateRow( row, data, device_status ) {
	
	var version = parseVersion( data.StatusFWR.Version );
	
	if ( version >= 51009 ) {//no json translations since 5.10.0j
		var rssi   = data.StatusSTS.Wifi.RSSI;
		var ssid   = data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Uptime;
	} else {
		var rssi   = data.StatusSTS.WLAN.RSSI;
		var ssid   = data.StatusSTS.WLAN.SSID;
		var uptime = data.StatusSTS.Laufzeit;
	}
	$( row ).find( ".version" ).html( data.StatusFWR.Version );
	$( row ).find( ".status" ).html( (
		                                 device_status == "ON" ? "AN" : "AUS"
	                                 ) );
	
	$( row ).find( ".rssi" ).html( rssi + "%" ).attr( "title", ssid );
	$( row ).find( ".runtime" ).html( "~" + uptime + "h" );
	
	
	$( row ).removeClass( "updating" );
}

var parseVersion = function ( versionString ) {
	versionString = versionString.replace( /\./g, "" );
	var last      = versionString.slice( -1 );
	if ( isNaN( last ) ) {
		versionString = versionString.replace(
			last,
			last.charCodeAt( 0 )
			- 97
			< 10
				? "0"
				  + last.charCodeAt( 0 )
				  - 97
				: last.charCodeAt( 0 )
				  - 97
		);
	} else {
		versionString = versionString + "00";
	}
	
	return versionString;
};