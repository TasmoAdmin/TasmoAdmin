$( document ).on( "ready", function () {
	ajaxLinks( "body" );
	$( "#content-holder" ).addClass( "loading" );
	$.get( "/pages/devices.php", function ( data ) {
		$( "#content-holder #content" ).html( data );
		$( "#content-holder" ).removeClass( "loading" );
		ajaxLinks( "#content-holder #content" );
		contentHandler();
		
	} );
	
	
} );


function ajaxLinks( content ) {
	$( content + " a" ).on( "click", function ( e ) {
		e.preventDefault();
		
		var page = $( this ).attr( "href" );
		console.log( "link clicked => " + page );
		
		$( "#content-holder" ).addClass( "loading" );
		$.get( page, function ( data ) {
			$( "#content-holder #content" ).html( data );
			$( "#content-holder" ).removeClass( "loading" );
			ajaxLinks( "#content-holder #content" );
			
			contentHandler();
			
		} );
	} );
	
	$( content + " form button" ).on( "click", function ( e ) {
		e.preventDefault();
		
		var form = $( this ).closest( "form" );
		var data = form.serialize();
		console.log( "form clicked => " + data );
		
		$( "#content-holder" ).addClass( "loading" );
		$.post( form.attr( "action" ), data, function ( data ) {
			$( "#content-holder #content" ).html( data );
			$( "#content-holder" ).removeClass( "loading" );
			ajaxLinks( "#content-holder #content" );
		} );
	} );
}

function contentHandler() {
	$( '#device-list tbody tr' ).each( function ( key, tr ) {
		
		console.log( "get status for " + $( tr ).data( "device_ip" ) );
		var device_ip = $( tr ).data( "device_ip" );
		var url       = "http://" + device_ip + "/cm?cmnd=Power1";
		$.getJSON( url, function ( data ) {
			           console.log( data );
			           if ( data.WARNING ) {
				           alert( device_ip + ": " + data.WARNING );
			           }
			           $( tr ).find( ".status" ).html( data.POWER );
		           }
		);
	} );
	
	$( '#device-list tbody tr td.status' ).on( "click", function ( e ) {
		e.preventDefault();
		var statusField = $( this );
		var device_ip   = $( this ).closest( "tr" ).data( "device_ip" );
		var url         = "http://" + device_ip + "/cm?cmnd=Power1%20toggle";
		console.log( "toggle status for " + device_ip );
		$.getJSON( url, function ( data ) {
			           console.log( data );
			           if ( data.WARNING ) {
				           alert( device_ip + ": " + data.WARNING );
			           }
			           statusField.html( data.POWER );
		           }
		);
	} );
	
	
	setTimeout( function () {
		//contentHandler();
	}, 1000 );
	
}