$( document ).on( "ready", function () {
	console.log( device_ips );
	device_ips = $.parseJSON( device_ips );
	
	var progressBox = $( "#progressbox" );
	
	
	log( "", "", "GLOBAL", "Update Start", "success" );
	
	$.each( device_ips, step1 );
	
	
	function step1( index, ip ) {
		log( ip, 1, "Starte Step 1", "info" );
		device_responses( ip, setOTAURL, ip, "MINIMAL", 1 );
	}
	
	
	function device_responses( ip, callback, p1, p2, step ) {
		var url = Sonoff.buildCmndUrl( ip, "Status 2" );
		log( ip, step, "Erreichbarkeit", "Prüfe Erreichbarkeit", "info" );
		$.ajax( {
			        dataType: "json",
			        url     : "/?doAjax=" + url,
			        timeout : 3 * 1000,
			        custom  : {
				        callback: callback,
			        },
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        log( ip, step, "Erreichbarkeit", "Fehler! - MSG =>" + data.WARNING, "error" );
				        }
				        log( ip, step, "Erreichbarkeit", "OK! - Aktuelle Version => "
				                                         + data.StatusFWR.Version, "success" );
				        this.custom.callback( p1, p2, step );
			        },
			        error   : function ( badData ) {
				        log( ip, step, "Erreichbarkeit", "Fehler! - Antwortet nicht!", "error" );
			        },
		        } );
	}
	
	function setOTAURL( ip, fwType, step ) {
		var fw = "";
		if ( fwType === "MINIMAL" ) {
			fw = $( "#ota_minimal_firmware_url" ).val();
		} else {
			fw = $( "#ota_new_firmware_url" ).val();
		}
		
		log( ip, step, "OTAURL", "Setze " + fwType + " OTA URL", "info" );
		var url = Sonoff.buildCmndUrl( ip, "OtaUrl " + fw );
		$.ajax( {
			        dataType: "json",
			        url     : "/?doAjax=" + url,
			        timeout : 6 * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        log( ip, step, "OTAURL", "Fehler! - MSG =>" + data.WARNING, "error" );
				        }
				        log( ip, 1, "OTAURL", fwType + " OTA URL gesetzt!", "success" );
				        startUpdate( ip, step );
				
			        },
			        error   : function ( badData ) {
				        log( ip, step, "OTAURL", "Fehler! - Antwortet nicht!", "error" );
				
			        },
		        } );
	}
	
	function startUpdate( ip, step ) {
		log( ip, step, "UPDATE", "Starte Update", "info" );
		var url = Sonoff.buildCmndUrl( ip, "Upgrade 1" );
		$.ajax( {
			        dataType: "json",
			        url     : "/?doAjax=" + url,
			        timeout : 3 * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        log( ip, step, "UPDATE", "Fehler! - MSG =>" + data.WARNING, "error" );
				        }
				        log( ip, step, "UPDATE", "Update angetoßen!", "info" );
				
				        checkUpdateDone( ip, step, 1 );
				
			        },
			        error   : function ( badData ) {
				        log( ip, step, "UPDATE", "Antwortet nicht!", "error" );
				
			        },
		        } );
		
	}
	
	function step2( ip ) {
		log( ip, 2, "GLOBAL", "Starte Step 2", "info" );
		
		device_responses( ip, setOTAURL, ip, "NEW FW", 2 );
	}
	
	function checkUpdateDone( ip, step, i ) {
		if ( i > 48 ) {
			log( ip, step, "CHECK UPDATE", "Gerät nach 5 Minuten immer noch nicht erreichbar!!!", "error" );
			return;
		}
		var sec = 60;
		if ( i > 1 ) {
			sec = 5;
		}
		log( ip, 1, "CHECK UPDATE", "Warte " + sec + " Sekunden auf Update", "info" );
		setTimeout(
			function () {
				var url = Sonoff.buildCmndUrl( ip, "Status 2" );
				$.ajax( {
					        dataType: "json",
					        url     : "/?doAjax=" + url,
					        timeout : 3 * 1000,
					
					        success: function ( data ) {
						        console.log( data );
						        if ( step == 1 ) {
							        log( ip, step, "CHECK UPDATE", "Update fertig!", "success" );
							        step2( ip );
						        } else {
							        log( ip, step, "DONE", "============= Update fertig! =============", "success" );
						        }
					        },
					        error  : function ( badData ) {
						        log( ip, step, "CHECK UPDATE", "Update noch nicht fertig!", "info" );
						        checkUpdateDone( ip, step, i + 1 );
					        },
				        } );
			}, sec * 1000
		);
	}
	
	
	function log( ip, step, block, msg, level ) {
		var dt   = new Date();
		var time = (
			           dt.getDate() < 10 ? "0" + dt.getDate() : dt.getDate()
		           )
		           + "-"
		           + dt.getMonth() + 1
		           + "-"
		           + dt.getFullYear()
		           + " "
		           + (
			           dt.getHours() < 10 ? "0" + dt.getHours() : dt.getHours()
		           )
		           + ":"
		           + (
			           dt.getMinutes() < 10 ? "0" + dt.getMinutes() : dt.getMinutes()
		           )
		           + ":"
		           + (
			           dt.getSeconds() < 10 ? "0" + dt.getSeconds() : dt.getSeconds()
		           )
		;
		
		var entry = "[" + time + "]";
		
		if ( ip !== "" ) {
			entry += "[" + ip + "]";
		}
		if ( step !== "" ) {
			entry += "[STEP-" + step + "]";
		}
		if ( block !== "" ) {
			entry += "[" + block + "]";
		}
		
		entry += " " + msg;
		
		progressBox.append( "<span class='" + level + "'>" + entry + "</span>" );
		$( "#content-holder" ).animate( { scrollTop: progressBox[ 0 ].scrollHeight }, 500 );
	}
} );