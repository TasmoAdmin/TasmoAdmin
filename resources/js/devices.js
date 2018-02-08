$( document ).on( "ready", function () {
	deviceTools();
	updateStatus();
	
	
	$( ".showmore" ).on( "change", function ( e ) {
		if ( $( this ).prop( "checked" ) ) {
			$( ".showmore" ).prop( "checked", true );
			Cookies.set( 'devices_show_more', '1' );
			$( "#device-list .more:not(.hidden)" ).show();
		} else {
			$( ".showmore" ).prop( "checked", false );
			Cookies.set( 'devices_show_more', '0' );
			$( "#device-list .more" ).hide();
		}
	} );
	
	if ( Cookies.get( 'devices_show_more' ) !== undefined && Cookies.get( 'devices_show_more' ) == "1" ) {
		$( ".showmore" ).prop( "checked", true );
		$( "#device-list .more:not(.hidden)" ).show();
	}
	
	$( '.table-responsive' ).attachDragger();
	//console.log( "5.10.0 => " + parseVersion( "5.10.0" ) );
	//console.log( "5.10.0g => " + parseVersion( "5.10.0g" ) );
	//console.log( "5.10.0h => " + parseVersion( "5.10.0h" ) );
	//console.log( "5.10.0i => " + parseVersion( "5.10.0i" ) );
	//console.log( "====" );
	//console.log( "5.10.0j => " + parseVersion( "5.10.0j" ) );
	//console.log( "5.10.0z => " + parseVersion( "5.10.0z" ) );
	//console.log( "5.11.1 => " + parseVersion( "5.11.1" ) );
	//console.log( "5.11.0 => " + parseVersion( "v5.11.0" ) );
	//console.log( "5.11.1b => " + parseVersion( "5.11.1b" ) );
	//console.log( "5.11.1d => " + parseVersion( "5.11.1d" ) );
	//console.log( "5.11.1z => " + parseVersion( "5.11.1z" ) );
	
	if ( refreshtime ) {
		console.log( "[Global][Refreshtime]" + refreshtime + "ms" );
		setInterval( function () {
			console.log( "refreshing" );
			updateStatus();
		}, refreshtime );
	} else {
		console.log( "[Global][Refreshtime] " + $.i18n( 'NO_REFRESH' ) + "" );
		
	}
	console.log( $.i18n( 'TEXT_LOADING' ) );
	
} );


function updateStatus() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "[Devices][updateStatus]get status from " + $( tr ).data( "device_ip" ) );
		var device_ip     = $( tr ).data( "device_ip" );
		var device_id     = $( tr ).data( "device_id" );
		var device_relais = $( tr ).data( "device_relais" );
		var device_group  = $( tr ).data( "device_group" );
		if ( !$( tr ).hasClass( "updating" ) ) {
			$( tr ).addClass( "updating" );
			
			if ( device_group == "multi" && device_relais > 1 ) {
				console.log( "[Devices][updateStatus]skip multi " + $( tr ).data( "device_ip" ) );
				return; //relais 1 will update all others
			}
			
			Sonoff.getStatus( device_ip, device_id, device_relais, function ( data ) {
				if ( data
				     && !data.ERROR
				     && !data.WARNING
				     && data
				        !== ""
				     && data
				        !== undefined
				     && data.statusText
				        === undefined ) {
					//console.log( "DATA => " + JSON.stringify( data ) );
					if ( device_group === "multi" ) {
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
					console.log( "ERROR => " + JSON.stringify( data ) );
					if ( device_group === "multi" ) {
						$( '#device-list tbody tr[data-device_group="multi"][data-device_ip="' + device_ip + '"]' )
							.each( function ( key, grouptr ) {
								
								$( grouptr )
									.find( ".status" )
									.find( "input" )
									.removeProp( "checked" )
									.parent()
									.addClass( "error" );
								$( grouptr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
								$( grouptr ).find( ".runtime span" ).html( $.i18n( 'ERROR' ) );
								$( grouptr ).find( ".version span" ).html( $.i18n( 'ERROR' ) );
								$( grouptr ).removeClass( "updating" );
							} );
					} else {
						
						$( tr ).find( ".status" ).find( "input" ).removeProp( "checked" ).parent().addClass( "error" );
						$( tr ).find( ".rssi span" ).html( $.i18n( 'ERROR' ) );
						$( tr ).find( ".runtime span" ).html( $.i18n( 'ERROR' ) );
						$( tr ).find( ".version span" ).html( $.i18n( 'ERROR' ) );
						$( tr ).removeClass( "updating" );
					}
				}
				
			} );
		}
	} );
	
	
};

function deviceTools() {
	$( '#device-list tbody tr td.status' ).on( "click", function ( e ) {
		e.preventDefault();
		var statusField   = $( this );
		var device_ip     = $( this ).closest( "tr" ).data( "device_ip" );
		var device_id     = $( this ).closest( "tr" ).data( "device_id" );
		var device_relais = $( this ).closest( "tr" ).data( "device_relais" );
		
		if ( statusField.find( "input" ).prop( "checked" ) ) {
			statusField.find( "input" ).removeProp( "checked" );
		} else {
			statusField.find( "input" ).prop( "checked", "checked" );
		}
		
		Sonoff.toggle( device_ip, device_id, device_relais, function ( data ) {
			if ( data && !data.ERROR && !data.WARNING ) {
				var device_status = data.POWER || eval( "data.POWER" + device_relais );
				if ( device_status == "ON" ) {
					statusField.find( "input" ).prop( "checked", "checked" );
				} else {
					statusField.find( "input" ).removeProp( "checked" );
				}
			} else {
				statusField.find( "input" ).removeProp( "checked" ).parent().addClass( "error" );
			}
		} );
		
		
	} );
	
	$( '#device-list tbody tr td a.delete' ).on( "click", function ( e ) {
		e.preventDefault();
		var actionUrl = $( this ).attr( "href" );
		var dialog    = $( '<div id="msg_dialog">' + $( this ).data( "dialog-text" ) + '</div>' )
			.appendTo( "body" );
		dialog.dialog( {
			               resizable: false,
			               dragable : false,
			               height   : "auto",
			               width    : "70%",
			               modal    : true,
			               title    : $( this ).data( "dialog-title" ),
			               buttons  :
				               [
					               {
						               text : $( this ).data( "dialog-btn-cancel-text" ),
						               icon : "ui-icon-closethick",
						               click: function () {
							               $( this ).dialog( "close" );
							               dialog.remove();
						               },
					               },
					               {
						               text : $( this ).data( "dialog-btn-ok-text" ),
						               icon : "ui-icon-check",
						               click: function () {
							               $( this ).dialog( "close" );
							               dialog.remove();
							               location.href = actionUrl;
						               },
						
					               },
				               ],
		               } );
		
	} );
	
	
	$( '#device-list tbody tr td a.restart-device' ).on( "click", function ( e ) {
		e.preventDefault();
		var device_id = $( this ).closest( "tr" ).data( "device_id" );
		Sonoff.generic( device_id, "Restart", 1 );
		
	} );
	
	
	$( document ).on( 'dblclick', '.dblcEdit span', function () {
		oriVal = $( this ).text();
		$( this ).text( "" ).addClass( "dont-update" );
		var w = oriVal.length * 10 + 20;
		console.log();
		input = $( "<input class='dblEdit-Input' type='text' style='width: " + w + "px;'>" );
		input.appendTo( $( this ) ).focus();
		
	} );
	
	
	$( document ).on( 'focusout keypress', '.dblEdit-Input', function ( e ) {
		if ( e.type === "keypress" && e.which !== 13 ) {
			return;
		}
		if ( input.val() != "" ) {
			var newvalue  = input.val();
			var device_id = $( this ).closest( "tr" ).data( "device_id" );
			var cmnd      = $( this ).closest( "td" ).data( "cmnd" );
			$( this ).hide();
			$( this )
				.parent()
				.removeClass( "dont-update" )
				.html( $.i18n( "TEXT_LOADING" ) )
				.removeClass( "dont-update" );
			Sonoff.updateConfig( device_id, cmnd, newvalue, updateStatus );
		} else {
			$( this ).parent().removeClass( "dont-update" ).text( oriVal ).removeClass( "dont-update" );
		}
		
	} );
}

function updateRow( row, data, device_status ) {
	
	var version = parseVersion( data.StatusFWR.Version );
	//console.log( "version => " + version );
	
	if ( version >= 510009 ) {//no json translations since 5.10.0j
		var rssi   = data.StatusSTS.Wifi.RSSI;
		var ssid   = data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Uptime;
	} else { //try german else use english
		var rssi   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI;
		var ssid   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Laufzeit != "undefined" ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime;
		
	}
	
	
	var temp = getTemp( data );
	
	if ( temp != "" ) {
		$( row ).find( ".temp span" ).html( temp );
		$( "#device-list .temp" ).removeClass( "hidden" );
	}
	var humidity = getHumidity( data );
	
	if ( humidity != "" ) {
		$( row ).find( ".humidity span" ).html( humidity );
		$( "#device-list .humidity" ).removeClass( "hidden" );
	}
	
	var idx = (
		data.idx ? data.idx : ""
	);
	if ( idx != "" ) {
		$( row ).find( ".idx span" ).html( idx );
		$( "#device-list .idx" ).removeClass( "hidden" ).show();
	}
	
	$( row ).find( ".version span" ).html( data.StatusFWR.Version );
	
	if ( device_status == "ON" ) {
		$( row ).find( ".status" ).find( "input" ).prop( "checked", "checked" ).parent().removeClass( "error" );
	} else {
		$( row ).find( ".status" ).find( "input" ).removeProp( "checked" ).parent().removeClass( "error" );
	}
	$( row ).find( ".rssi span" ).html( rssi + "%" ).attr( "title", ssid );
	$( row ).find( ".runtime span" ).html( "~" + uptime + "h" );
	
	
	//MORE
	if ( !$( row ).find( ".hostname span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".hostname span" ).html( data.StatusNET.Hostname !== undefined ? data.StatusNET.Hostname : "?" );
	}
	
	if ( !$( row ).find( ".mac span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".mac span" ).html( data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?" );
	}
	
	if ( !$( row ).find( ".mqtt span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".mqtt span" ).html( data.StatusMQT !== undefined ? "1" : "0" );
	}
	
	if ( !$( row ).find( ".poweronstate span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".poweronstate span" ).html( data.Status.PowerOnState
		                                            !== undefined
			                                            ? data.Status.PowerOnState
			                                            : "?" );
	}
	
	if ( !$( row ).find( ".ledstate span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".ledstate span" ).html( data.Status.LedState !== undefined ? data.Status.LedState : "?" );
	}
	
	
	if ( !$( row ).find( ".savedata span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".savedata span" ).html( data.Status.SaveData !== undefined ? data.Status.SaveData : "?" );
	}
	
	
	if ( !$( row ).find( ".sleep span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".sleep span" ).html( data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?" );
	}
	
	
	$( row ).find( ".bootcount span" ).html( data.StatusPRM.BootCount
	                                         !== undefined
		                                         ? data.StatusPRM.BootCount
		                                         : "?" );
	$( row ).find( ".savecount span" ).html( data.StatusPRM.SaveCount
	                                         !== undefined
		                                         ? data.StatusPRM.SaveCount
		                                         : "?" );
	$( row ).find( ".log span" ).html( (
		                                   data.StatusLOG.SerialLog !== undefined ? data.StatusLOG.SerialLog : "?"
	                                   )
	                                   + "|"
	                                   + (
		                                   data.StatusLOG.WebLog !== undefined ? data.StatusLOG.WebLog : "?"
	                                   )
	                                   + "|"
	                                   + (
		                                   data.StatusLOG.SysLog !== undefined ? data.StatusLOG.SysLog : "?"
	                                   ) );
	
	
	if ( !$( row ).find( ".wificonfig span" ).hasClass( "dont-update" ) ) {
		$( row ).find( ".wificonfig span" ).html( data.StatusNET.WifiConfig
		                                          !== undefined
			                                          ? data.StatusNET.WifiConfig
			                                          : "?" );
	}
	
	$( row ).find( ".vcc span" ).html( data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?" );
	
	
	$( row ).removeClass( "updating" );
}


