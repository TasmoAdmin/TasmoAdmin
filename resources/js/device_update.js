var ajaxTimeout = 8;
$( document ).on( "ready", function () {
	console.log( device_ids );
	device_ids = $.parseJSON( device_ids );
	
	var progressBox = $( "#progressbox" );
	
	
	log( "", "", "GLOBAL", "Update Start", "success" );
	
	$.each( device_ids, step1 );
	
	
	function step1( index, id ) {
		log( id, 1, "Starte Step 1", "info" );
		device_responses( id, setOTAURL, id, "MINIMAL", 1 );
	}
	
	
	function device_responses( id, callback, p1, p2, step, tries ) {
		var tries = tries || 1;
		var cmnd  = "Status 2";
		log( id, step, "Erreichbarkeit", "Prüfe Erreichbarkeit Versuch " + tries, "info" );
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjax",
			        data    : {
				        id  : id,
				        cmnd: cmnd,
			        },
			        timeout : ajaxTimeout * 1000,
			        custom  : {
				        callback: callback,
			        },
			        success : function ( data ) {
				        console.log( data );
				        if ( data && !data.ERROR ) {
					        if ( data.WARNING ) {
						        log( id, step, "Erreichbarkeit", "Fehler! - MSG =>" + data.WARNING, "error" );
					        }
					        log( id, step, "Erreichbarkeit", "OK! - Aktuelle Version => "
					                                         + data.StatusFWR.Version, "success" );
					        this.custom.callback( p1, p2, step );
				        } else {
					        log( id, step, "Erreichbarkeit", "Fehler! - Antwortet nicht! => " + data.ERROR, "error" );
					        if ( tries < 3 ) {
						        tries = tries + 1;
						        device_responses( id, callback, p1, p2, step, tries );
					        }
				        }
			        },
			        error   : function ( badData ) {
				        log( id, step, "Erreichbarkeit", "Fehler! - Antwortet nicht!", "error" );
				        if ( tries < 3 ) {
					        tries = tries + 1;
					        device_responses( id, callback, p1, p2, step, tries );
				        }
			        },
		        } );
	}
	
	function setOTAURL( id, fwType, step ) {
		var fw = "";
		if ( fwType === "MINIMAL" ) {
			fw = $( "#ota_minimal_firmware_url" ).val();
		} else {
			fw = $( "#ota_new_firmware_url" ).val();
		}
		
		log( id, step, "OTAURL", "Setze " + fwType + " OTA URL", "info" );
		var cmnd = "OtaUrl " + fw;
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjax",
			        data    : {
				        id  : id,
				        cmnd: cmnd,
			        },
			        timeout : ajaxTimeout * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        log( id, step, "OTAURL", "Fehler! - MSG =>" + data.WARNING, "error" );
				        }
				        log( id, 1, "OTAURL", fwType + " OTA URL gesetzt!", "success" );
				        startUpdate( id, step );
				
			        },
			        error   : function ( badData ) {
				        log( id, step, "OTAURL", "Fehler! - Antwortet nicht!", "error" );
				
			        },
		        } );
	}
	
	function startUpdate( id, step ) {
		log( id, step, "UPDATE", "Starte Update", "info" );
		var cmnd = "Upgrade 1";
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjax",
			        data    : {
				        id  : id,
				        cmnd: cmnd,
			        },
			        timeout : ajaxTimeout * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data && !data.ERROR ) {
					        if ( data.WARNING ) {
						        log( id, step, "UPDATE", "Fehler! - MSG =>" + data.WARNING, "error" );
					        } else {
						        log( id, step, "UPDATE", "Update angetoßen!", "info" );
					        }
					        checkUpdateDone( id, step, 1 );
				        } else {
					        log( id, step, "UPDATE", "Antwortet nicht!", "error" );
				        }
				
			        },
			        error   : function ( badData ) {
				        log( id, step, "UPDATE", "Antwortet nicht!", "error" );
				
			        },
		        } );
		
	}
	
	function step2( id ) {
		log( id, 2, "GLOBAL", "Starte Step 2", "info" );
		
		device_responses( id, setOTAURL, id, "NEW FW", 2 );
	}
	
	function checkUpdateDone( id, step, i ) {
		if ( i > 48 ) {
			log( id, step, "CHECK UPDATE", "Gerät nach 5 Minuten immer noch nicht erreichbar!!!", "error" );
			return;
		}
		var sec = 60;
		if ( i > 1 ) {
			sec = 30;
		}
		log( id, step, "CHECK UPDATE", "Warte " + sec + " Sekunden auf Update", "info" );
		setTimeout(
			function () {
				var cmnd = "Status 2";
				$.ajax( {
					        dataType: "json",
					        url     : "index.php?doAjax",
					        data    : {
						        id  : id,
						        cmnd: cmnd,
					        },
					        timeout : ajaxTimeout * 1000,
					
					        success: function ( data ) {
						        console.log( data );
						        if ( data && !data.ERROR ) {
							        if ( step == 1 ) {
								        log( id, step, "CHECK UPDATE", "Update fertig!", "success" );
								        step2( id );
							        } else {
								        log(
									        id,
									        step,
									        "DONE",
									        "============= Update fertig! =============",
									        "success"
								        );
							        }
						        } else {
							        log( id, step, "CHECK UPDATE", "Update noch nicht fertig!", "info" );
							        checkUpdateDone( id, step, i + 1 );
						        }
					        },
					        error  : function ( badData ) {
						        log( id, step, "CHECK UPDATE", "Update noch nicht fertig!", "info" );
						        checkUpdateDone( id, step, i + 1 );
					        },
				        } );
			}, sec * 1000
		);
	}
	
	
	function log( id, step, block, msg, level ) {
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
		
		if ( id !== "" ) {
			entry += "[" + id + "]";
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