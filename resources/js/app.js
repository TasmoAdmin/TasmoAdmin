$( document ).on( "ready", function () {
	deviceHandler();
	
	
} );


function deviceHandler() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "get status for " + $( tr ).data( "device_ip" ) );
		var device_ip = $( tr ).data( "device_ip" );
		var url       = "http://" + device_ip + "/cm?cmnd=Power1";
		
		
		$.ajax( {
			        dataType: "json",
			        url     : url,
			        timeout : 3 * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        alert( device_ip + ": " + data.WARNING );
				        }
				        $( tr ).find( ".status" ).html( data.POWER );
			        },
			        error   : function ( badData ) {
				        $( tr ).find( ".status" ).html( "Fehler" );
			        },
		        } );
		
		
	} );
	
	$( '#device-list tbody tr td.status' ).on( "click", function ( e ) {
		e.preventDefault();
		var statusField = $( this );
		var device_ip   = $( this ).closest( "tr" ).data( "device_ip" );
		var url         = "http://" + device_ip + "/cm?cmnd=Power1%20toggle";
		console.log( "toggle status for " + device_ip );
		$.ajax( {
			        dataType: "json",
			        url     : url,
			        timeout : 3 * 1000,
			        success : function ( data ) {
				        console.log( data );
				        if ( data.WARNING ) {
					        alert( device_ip + ": " + data.WARNING );
				        }
				        if ( data.POWER ) {
					        statusField.html( data.POWER );
				        } else {
					        statusField.html( "Fehler" );
				        }
			        },
			        error   : function ( badData ) {
				        statusField.html( "Fehler" );
			        },
		        } );
	} );
	
	
	setTimeout( function () {
		//deviceHandler();
	}, 1000 );
	
}