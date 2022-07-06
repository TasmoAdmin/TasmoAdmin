$( document ).on( "ready", function () {
	updateAllStatus();
} );

function updateAllStatus() {
	
	var device_holder = $( "#device_details" );
	
	
	if ( !device_holder.hasClass( "updating" ) ) {
		device_holder.addClass( "updating" );
		
		console.log( "[Devices][updateAllStatus]START" );
		
		var timeout = device_holder.find( 'card' ).length * 15; //max 12 sec per device
		
		Sonoff.getAllStatus( timeout, function ( result ) {
			                     device_holder.find( '.card' ).each( function ( key, card ) {
				                     var device_id     = $( card ).data( "device_id" );
				                     var device_relais = $( card ).data( "device_relais" );
				                     var device_group  = $( card ).data( "device_group" );
				                     var data          = result[ device_id ] || undefined;
				                     if ( data !== undefined
				                          && !$.isEmptyObject( data )
				                          && !data.ERROR
				                          && !data.WARNING
				                          && data
				                          !== ""
				                          && data
				                          !== undefined
				                          && data.statusText
				                          === undefined ) {
					                     console.log( "[LIST][updateAllStatus][" + device_id + "]MSG => " + JSON.stringify( data ) );
					
					                     var device_status = data.StatusSTS.POWER || eval( "data.StatusSTS.POWER" + device_relais );
					
					
					                     updateCard( $( card ), data, device_status );
					
				                     } else {
					                     console.log( "[LIST][updateAllStatus]["
					                                  + device_id
					                                  + "][ERROR] DATA => "
					                                  + JSON.stringify( data ) );
					
					
					                     if ( $( card ).hasClass( "toggled" ) ) {
						                     $( card ).removeClass( "toggled" );
					                     } else {
						                     $( card ).find( ".status" ).find( "input" )
						                     //.removeProp( "checked" )
						                              .parent().addClass( "error" );
					                     }
					
					                     var msg = $.i18n( 'ERROR' );
					                     if ( data !== undefined ) {
						                     if ( data.ERROR !== undefined ) {
							                     msg = data.ERROR;
						                     } else if ( data.WARNING !== undefined ) {
							                     msg = data.WARNING;
						                     }
						                     else if ( data.statusText !== undefined ) {
							                     msg = data.statusText;
						                     }
					                     } else {
						                     msg = "data is empty";
					                     }
					
					                     $( card ).attr(
						                     "data-original-title",
						                     msg
					                     ).attr( "data-toggle", "tooltip" ).tooltip( {
						                                                                 html : true,
						                                                                 delay: 700
					                                                                 } );
					
					
				                     }
				
				
			                     } );
			
			                     device_holder.removeClass( "updating" );
			
		                     }
		);
	} else {
		console.log( "[Devices][updateAllStatus]SKIP" );
	}
	
};


function updateCard( card, data, device_status ) {
	
	var version = parseVersion( data.StatusFWR.Version );
	//console.log( "version => " + version );
	
	if ( version >= 510009 ) {//no json translations since 5.10.0j
		var rssi   = data.StatusSTS.Wifi.RSSI;
		var ssid   = data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Uptime;
	} else { //try german else use english
		var rssi   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI;
		var ssid   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId;
		var uptime = data.StatusSTS.Laufzeit ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime;
		
	}
	var deviceData = {};
	
	var energyPower = getEnergyPower( data );
	
	if ( energyPower !== "" ) {
		
		deviceData.energyPower = energyPower;
	}
	
	var temp = getTemp( data );
	
	if ( temp !== "" ) {
		deviceData.temp = temp;
	}
	
	
	var humidity = getHumidity( data );
	
	if ( humidity !== "" ) {
		deviceData.humidity = humidity;
	}
	
	var pressure = getPressure( data );
	
	if ( pressure !== "" ) {
		deviceData.pressure = pressure;
	}
	
	var distance = getDistance( data );
	
	if ( distance !== "" ) {
		deviceData.distance = distance;
	}
	
	var gas = getGas( data );
	
	if ( gas !== "" ) {
		deviceData.gas = gas;
	}
	
	var idx = (
		data.idx ? data.idx : ""
	);
	if ( idx !== "" ) {
		deviceData.idx = idx;
	}
	
	deviceData.version = data.StatusFWR.Version;
	
	
	var img = $( card ).find( ".devices-switch-container img" );
	var src = _RESOURCESURL_ + "img/device_icons/"
	          + img.data( "icon" )
	          + "_%pw.png?v=160";
	
	
	src = src.replace( "%pw", device_status.toLowerCase() );
	img.attr( "src", src );
	
	//if ( $( card ).hasClass( "toggled" ) ) {
	//	$( card ).removeClass( "toggled" );
	//} else {
	//	if ( device_status === "ON" ) {
	//		$( card ).find( ".devices-switch-container img" ).src();
	//	} else {
	//		$( card ).find( ".status" ).find( "input" ).removeProp( "checked" ).parent().removeClass( "error" );
	//	}
	//}
	
	var signalStrength = "bad";
	
	if ( rssi >= 25 ) {
		signalStrength = "weak";
	}
	if ( rssi >= 50 ) {
		signalStrength = "medium";
	}
	if ( rssi >= 75 ) {
		signalStrength = "strong";
	}
	
	
	$( card ).find( ".device-rssi svg" ).addClass( "ta-wifi-" + signalStrength ).removeClass( "searching error" );
	$( card ).find( ".device-rssi" )
	         .data( "original-title", rssi + "%" )
	         .attr( "title", rssi + "%" )
	         .tooltip( '_fixTitle' );
	
	var startup = (
		(
			data.StatusPRM.StartupDateTimeUtc !== undefined
			? data.StatusPRM.StartupDateTimeUtc
			: (
				data.StatusPRM.StartupUTC !== undefined
				? data.StatusPRM.StartupUTC
				: ""
			)
		)
	);
	//console.log( startup );
	if ( startup !== "" ) {
		
		//var startupdatetime = startup.replace( 'T', ' ' );
		var startupdatetime = startup + "Z".replace( /-/g, "/" );
		//console.log( startupdatetime );
		startupdatetime     = new Date( startupdatetime );
		//console.log( startupdatetime );
		//startupdatetime.setTime( startupdatetime.getTime() + (
		//	startupdatetime.getTimezoneOffset()
		//) * -1 * 60 * 1000 );
		//console.log( startupdatetime );
		var now     = new Date();
		var sec_num = (
			              now - startupdatetime
		              ) / 1000;
		var days    = Math.floor( sec_num / (
			3600 * 24
		) );
		var hours   = Math.floor( (
			                          sec_num - (
				                          days * (
				                          3600 * 24
				                          )
			                          )
		                          ) / 3600 );
		var minutes = Math.floor( (
			                          sec_num - (
				                          days * (
				                          3600 * 24
				                          )
			                          ) - (
				                          hours * 3600
			                          )
		                          ) / 60 );
		var seconds = Math.floor( sec_num - (
			days * (
			3600 * 24
			)
		) - (
			                          hours * 3600
		                          ) - (
			                          minutes * 60
		                          ) );
		
		uptime = (
			         days !== 0 ? days + $.i18n( 'UPTIME_SHORT_DAY' ) : ""
		         ) + " " + (
			         hours !== 0 || days !== 0 ? hours + $.i18n( 'UPTIME_SHORT_HOUR' ) : ""
		         ) + " " + (
			         minutes !== 0 || hours !== 0 || days !== 0 ? minutes + $.i18n( 'UPTIME_SHORT_MIN' ) : ""
		         ) + " " + (
			         seconds
			         !== 0
			         || minutes
			            !== 0
			         || hours
			            !== 0
			         ? seconds
			     + $.i18n( 'UPTIME_SHORT_SEC' )
			         : "-"
		         );
		
		uptime = $.trim( uptime );
		
		
		var uptimeString = startupdatetime.toLocaleString( $( "html" ).attr( "lang" ) + "-" + $( "html" )
			.attr( "lang" )
			.toUpperCase(), { hour12: false }
		);
		
		//console.log( uptimeString );
		$( card ).find( ".runtime" )
		         .html( uptime )
		         .data( "original-title", uptimeString )
		         .attr( "title", uptimeString )
		         .tooltip( '_fixTitle' );
		$( card ).find( ".runtime" ).fadeIn();
		
	} else {
		//console.log( uptime );
		$( card ).find( ".runtime" ).html( uptime + "h" );
	}
	
	
	//MORE
	if ( !$( card ).find( ".hostname span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".hostname span" ).html( data.StatusNET.Hostname
		                                         !== undefined
		                                         ? data.StatusNET.Hostname
		                                         : "?" );
	}
	
	if ( !$( card ).find( ".mac span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".mac span" ).html( data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?" );
	}
	
	if ( !$( card ).find( ".mqtt span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".mqtt span" ).html( data.StatusMQT !== undefined ? "1" : "0" );
	}
	
	if ( !$( card ).find( ".poweronstate span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".poweronstate span" ).html( data.Status.PowerOnState
		                                             !== undefined
		                                             ? data.Status.PowerOnState
		                                             : "?" );
	}
	
	if ( !$( card ).find( ".ledstate span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".ledstate span" ).html( data.Status.LedState !== undefined ? data.Status.LedState : "?" );
	}
	
	
	if ( !$( card ).find( ".savedata span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".savedata span" ).html( data.Status.SaveData !== undefined ? data.Status.SaveData : "?" );
	}
	
	
	if ( !$( card ).find( ".sleep span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".sleep span" ).html( data.StatusPRM.Sleep
		                                      !== undefined
		                                      ? data.StatusPRM.Sleep
		                                        + "ms"
		                                      : "?" );
	}
	
	
	$( card ).find( ".bootcount span" ).html( data.StatusPRM.BootCount
	                                          !== undefined
	                                          ? data.StatusPRM.BootCount
	                                          : "?" );
	$( card ).find( ".savecount span" ).html( data.StatusPRM.SaveCount
	                                          !== undefined
	                                          ? data.StatusPRM.SaveCount
	                                          : "?" );
	$( card ).find( ".log span" ).html( (
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
	
	
	if ( !$( card ).find( ".wificonfig span" ).hasClass( "dont-update" ) ) {
		$( card ).find( ".wificonfig span" ).html( data.StatusNET.WifiConfig
		                                           !== undefined
		                                           ? data.StatusNET.WifiConfig
		                                           : "?" );
	}
	
	$( card ).find( ".vcc span" ).html( data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?" );
	
	$( '.doubleScroll-scroll' ).css( {
		                                 width: $( "#device-list" ).width()
	                                 } ).parent().trigger( "resize" );
	
	
	//console.log( deviceData );
	$.each( deviceData, function ( key, value ) {
		console.log( key + " => " + value );
		if ( card.find( ".device-data." + key ).length > 0 ) {
			card.find( ".device-data." + key ).html( value ).fadeIn();
		}
	} );
	
	$( card ).removeClass( "updating" );
}
