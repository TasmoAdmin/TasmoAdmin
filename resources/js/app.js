var Sonoff;
var refreshtime = false;
$( document ).on( "ready", function () {
	checkNightmode( nightmodeconfig );
	
	var $lang    = $( "html" ).attr( "lang" );
	var i18nfile = _BASEURL_ + 'tmp/cache/i18n/json_i18n_' + $lang + '.cache.json';
	console.log( i18nfile );
	$.ajax( {
		        dataType: "json",
		        url     : i18nfile,
		        async   : false,
		        success : function ( data ) {
			
			        $.i18n().load( data );
		        },
	        } );
	/**
	 * Sonoff Handler
	 * @type {Sonoff}
	 */
	Sonoff = new Sonoff( { timeout: 15 } );
	
	$( '[title][title!=""]' ).tooltip( {
		                                   html : true,
		                                   delay: 700,
	                                   } );
	
	
	$( '.custom-file-input' ).on( 'change', function () {
		var filename = $( this ).val();
		filename     = filename.replace( /^.*\\/, "" );
		filename     = filename.match( /[^\\/]*$/ )[ 0 ];
		$( this ).next().html( filename );
	} );
	
	$( "a.reload" ).on( "click", function ( e ) {
		e.preventDefault();
		window.location.href = window.location.href;
	} );
	//$( "select#language-switch" ).selectmenu( "option", "width", "80px" );
	
	var appendLoading = function ( elem, replace ) {
		var replace = replace || false;
		var loader  = $( '<div>', { class: "loader" } ).append(
			$( 'img', { src: _RESOURCESURL_ + "img/loading.gif" } ) );
		
		if ( replace ) {
			$( elem ).html( loader );
		} else {
			$( elem ).append( loader );
		}
	};
	
	//$( '.hamburger' ).click( function () {
	//	$( "#navi" ).toggleClass( "show" );
	//	$( '.hamburger' ).toggleClass( "open" );
	//} );
	
	if ( $( "#content" ).data( "refreshtime" ) !== "none" ) {
		refreshtime = $( "#content" ).data( "refreshtime" ) * 1000;
	}
	
	$( "input[type=\"number\"]" ).keydown( function ( e ) {
		// Allow: backspace, delete, tab, escape, enter and .
		if ( $.inArray( e.keyCode, [ 46, 8, 9, 27, 13, 110, 190 ] ) !== -1 ||
		     // Allow: Ctrl+A, Command+A
		     (
			     e.keyCode === 65 && (
				     e.ctrlKey === true || e.metaKey === true
			     )
		     ) ||
		     // Allow: home, end, left, right, down, up
		     (
			     e.keyCode >= 35 && e.keyCode <= 40
		     ) ) {
			// let it happen, don't do anything
			return;
		}
		// Ensure that it is a number and stop the keypress
		if ( (
			     e.shiftKey || (
			     e.keyCode < 48 || e.keyCode > 57
			     )
		     ) && (
			     e.keyCode < 96 || e.keyCode > 105
		     ) ) {
			e.preventDefault();
		}
	} );
	
	$( "select#language-switch" ).on( "change", function ( event, ui ) {
		var optionSelected = $( "option:selected", this );
		var valueSelected  = this.value;
		
		var curUrl           = window.location.toString() + "/" + valueSelected + "/";
		window.location.href = curUrl;
		
		// var curUrl = window.location.toString();
		// curUrl     = curUrl.replace( /[\?\&][a-z]2/g, "" );
		// console.log( curUrl );
		//
		// window.location.href = curUrl + (
		//     curUrl.indexOf( "?" ) !== -1 ? "&" : "?"
		// ) + "lang=" + valueSelected;
	} );
	
	window.setInterval( function () {
		console.log( "checknightmode" );
		checkNightmode( nightmodeconfig );
	}, 15 * 60 * 1000 );
} );


function notifyMe( msg, title ) {
	var title = title || "";
	if ( title != "" ) {
		title = " - " + title;
	}
	
	var icon = "./resources/img/favicons/apple-icon-180x180.png";
	
	// Let's check if the browser supports notifications
	if ( !(
			"Notification" in window
		) ) {
		return;
	}
	
	
	// Let's check whether notification permissions have already been granted
	else if ( Notification.permission === "granted" ) {
		// If it's okay let's create a notification
		var notification = new Notification( "SonWEB" + title, { body: msg, icon: icon } );
		setTimeout( notification.close.bind( notification ), 3000 );
	}
	
	// Otherwise, we need to ask the user for permission
	else if ( Notification.permission !== 'denied' ) {
		Notification.requestPermission( function ( permission ) {
			// If the user accepts, let's create a notification
			if ( permission === "granted" ) {
				var notification = new Notification( "SonWEB" + title, { body: msg } );
				setTimeout( notification.close.bind( notification ), 3000 );
			}
		} );
	}
	
	// Finally, if the user has denied notifications and you
	// want to be respectful there is no need to bother them any more.
}


$.fn.attachDragger = function () {
	var attachment = false, lastPosition, position, difference;
	$( $( this ).selector ).on( "mousedown mouseup mousemove", function ( e ) {
		if ( e.type == "mousedown" && !$( e.target ).hasClass( "tablesaw-cell-content" ) ) {
			attachment = true, lastPosition = [ e.clientX, e.clientY ];
			$( ".tablesaw-cell-content" ).addClass( "dontselect" );
		}
		if ( e.type == "mouseup" ) {
			attachment = false;
			$( ".tablesaw-cell-content" ).removeClass( "dontselect" );
		}
		if ( e.type == "mousemove" && attachment == true ) {
			position   = [ e.clientX, e.clientY ];
			difference = [
				(
					position[ 0 ] - lastPosition[ 0 ]
				),
				(
					position[ 1 ] - lastPosition[ 1 ]
				),
			];
			$( this ).scrollLeft( $( this ).scrollLeft() - difference[ 0 ] );
			$( this ).scrollTop( $( this ).scrollTop() - difference[ 1 ] );
			lastPosition = [ e.clientX, e.clientY ];
		}
	} );
	$( window ).on( "mouseup", function () {
		attachment = false;
		$( ".tablesaw-cell-content" ).removeClass( "dontselect" );
	} );
};


var parseVersion = function ( versionString ) {
	versionString = versionString.replace( "-minimal", "" ).replace( /\./g, "" );
	
	var last = versionString.slice( -1 );
	if ( isNaN( last ) ) {
		versionString = versionString.replace(
			last,
			(
				last.charCodeAt( 0 ) - 97 < 10
					? "0" + (
					last.charCodeAt( 0 ) - 97
				)
					: last.charCodeAt( 0 ) - 97
			)
		);
	} else {
		versionString = versionString + "00";
	}
	
	return versionString;
};


function getTemp( data ) {
	var temp = [];
	
	if ( data.StatusSNS.TempUnit == undefined ) {
		data.StatusSNS.TempUnit = "F";
	}
	
	if ( data.StatusSNS.DS18B20 !== undefined ) {
		temp.push( (
			           data.StatusSNS.DS18B20.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.DS18x20 !== undefined ) {
		if ( data.StatusSNS.DS18x20.DS1 !== undefined ) {
			temp.push( (
				           data.StatusSNS.DS18x20.DS1.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
		if ( data.StatusSNS.DS18x20.DS2 !== undefined ) {
			temp.push( (
				           data.StatusSNS.DS18x20.DS1.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
	}
	if ( data.StatusSNS.DHT11 !== undefined ) {
		temp.push( (
			           data.StatusSNS.DHT11.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.AM2301 !== undefined ) {
		temp.push( (
			           data.StatusSNS.AM2301.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.SHT3X !== undefined ) {
		temp.push( (
			           data.StatusSNS.SHT3X.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS[ "SHT3X-0x45" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "SHT3X-0x45" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.BMP280 !== undefined ) {
		temp.push( (
			           data.StatusSNS.BMP280.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.BME280 !== undefined ) {
		temp.push( (
			           data.StatusSNS.BME280.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.SI7021 !== undefined ) {
		temp.push( (
			           data.StatusSNS.SI7021.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS.HTU21 !== undefined ) {
		temp.push( (
			           data.StatusSNS.HTU21.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	
	//console.log( temp );
	
	return temp.join( "<br/>" );
}

function getHumidity( data ) {
	var humi = [];
	
	if ( data.StatusSNS.AM2301 !== undefined ) {
		if ( data.StatusSNS.AM2301.Humidity !== undefined ) {
			humi.push( data.StatusSNS.AM2301.Humidity + "%" );
		}
	}
	if ( data.StatusSNS.BME280 !== undefined ) {
		if ( data.StatusSNS.BME280.Humidity !== undefined ) {
			humi.push( data.StatusSNS.BME280.Humidity + "%" );
		}
	}
	if ( data.StatusSNS.DHT11 !== undefined ) {
		if ( data.StatusSNS.DHT11.Humidity !== undefined ) {
			humi.push( data.StatusSNS.DHT11.Humidity + "%" );
		}
	}
	if ( data.StatusSNS.SHT3X !== undefined ) {
		if ( data.StatusSNS.SHT3X.Humidity !== undefined ) {
			humi.push( data.StatusSNS.SHT3X.Humidity + "%" );
		}
	}
	if ( data.StatusSNS[ "SHT3X-0x45" ] !== undefined ) {
		if ( data.StatusSNS[ "SHT3X-0x45" ].Humidity !== undefined ) {
			humi.push( data.StatusSNS[ "SHT3X-0x45" ].Humidity + "%" );
		}
	}
	if ( data.StatusSNS.SI7021 !== undefined ) {
		if ( data.StatusSNS.SI7021.Humidity !== undefined ) {
			humi.push( data.StatusSNS.SI7021.Humidity + "%" );
		}
	}
	if ( data.StatusSNS.HTU21 !== undefined ) {
		if ( data.StatusSNS.HTU21.Humidity !== undefined ) {
			humi.push( data.StatusSNS.HTU21.Humidity + "%" );
		}
	}
	
	//console.log( humi );
	
	return humi.join( "<br/>" );
}

function getPressure( data ) {
	var press = [];
	
	if ( data.StatusSNS.BME280 !== undefined ) {
		if ( data.StatusSNS.BME280.Pressure !== undefined ) {
			press.push( data.StatusSNS.BME280.Pressure + "&nbsp;hPa" );
		}
	}
	if ( data.StatusSNS.BMP280 !== undefined ) {
		if ( data.StatusSNS.BMP280.Pressure !== undefined ) {
			press.push( data.StatusSNS.BMP280.Pressure + "&nbsp;hPa" );
		}
	}
	
	//console.log( press );
	
	return press.join( "<br/>" );
}


function checkNightmode( config ) {
	var config = config || "auto";
	
	var currentTime = new Date();
	var hour        = currentTime.getHours();
	
	//console.log( "check Nightmode => " + hour + "h - " + config );
	
	
	if ( config === "disable" ) {
		$( "body" ).removeClass( "nightmode" );
	} else {
		if ( config === "auto" ) {
			if ( hour >= 18 || hour <= 8 ) {
				$( "body" ).addClass( "nightmode" );
			} else {
				$( "body" ).removeClass( "nightmode" );
			}
		} else if ( config === "always" ) {
			$( "body" ).addClass( "nightmode" );
		}
	}
}

Date.prototype.addHours = function ( h ) {
	this.setHours( this.getHours() + h );
	return this;
};


jQuery.fn.shake = function ( intShakes, intDistance, intDuration ) {
	this.each( function () {
		$( this ).css( "position", "relative" );
		for ( var x = 1; x <= intShakes; x++ ) {
			$( this ).animate(
				{
					left: (
						intDistance * -1
					),
				},
				(
					(
						(
						intDuration / intShakes
						) / 4
					)
				)
			)
			         .animate(
				         { left: intDistance },
				         (
					         (
					         intDuration / intShakes
					         ) / 2
				         )
			         )
			         .animate(
				         { left: 0 },
				         (
					         (
						         (
						         intDuration / intShakes
						         ) / 4
					         )
				         )
			         );
		}
	} );
	return this;
};
