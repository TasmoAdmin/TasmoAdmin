var Sonoff;
var refreshtime = false;
var nightmode   = false;
$( document ).on( "ready", function () {
	
	var $lang    = $( "html" ).attr( "lang" );
	var i18nfile = _BASEURL_ + 'tmp/cache/i18n/json_i18n_' + $lang + '.cache.json';
	//console.log( i18nfile );
	
	$.ajax( {
		        dataType: "json",
		        url     : i18nfile,
		        async   : false,
		        success : function ( data ) {
			
			        $.i18n().load( data );
		        }
	        } );
	
	
	checkNightmode( nightmodeconfig );
	checkForUpdate( true );
	
	
	$( '.double-scroll' ).doubleScroll(
		{
			contentElement     : "#device-list", // Widest element, if not specified first child element will be used
			scrollCss          : {
				'overflow-x': 'auto',
				'overflow-y': 'hidden'
			},
			contentCss         : {
				'overflow-x': 'auto',
				'overflow-y': 'hidden'
			},
			onlyIfScroll       : true, // top scrollbar is not shown if the bottom one is not present
			resetOnWindowResize: true, // recompute the top ScrollBar requirements when the window is resized
			timeToWaitForResize: 1
		}
	);
	/**
	 * Sonoff Handler
	 * @type {Sonoff}
	 */
	
	
	
	Sonoff = new Sonoff( { timeout: 15 } );
	
	$( '[title][title!=""]' ).tooltip( {
		                                   html : true,
		                                   delay: 300
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
	
	
	$( "#versionHolder" ).on( "click", function ( e ) {
		e.preventDefault();
		if ( $( this ).hasClass( "update-now" ) || $( "#versionHolder" ).data( "update-check" ) == "0" ) {
			window.location.href = _BASEURL_ + "selfupdate";
		} else {
			checkForUpdate( false );
		}
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
			// var it happen, don't do anything
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
		curUrl               = curUrl.replace( /([^:]\/)\/+/g, "$1" );
		window.location.href = curUrl;
		
		// var curUrl = window.location.toString();
		// curUrl     = curUrl.replace( /[\?\&][a-z]2/g, "" );
		// console.log( curUrl );
		//
		// window.location.href = curUrl + (
		//     curUrl.indexOf( "?" ) !== -1 ? "&" : "?"
		// ) + "lang=" + valueSelected;
	} );
	
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
		var notification = new Notification( "TasmoAdmin" + title, { body: msg, icon: icon } );
		setTimeout( notification.close.bind( notification ), 3000 );
	}
	
	// Otherwise, we need to ask the user for permission
	else if ( Notification.permission !== 'denied' ) {
		Notification.requestPermission( function ( permission ) {
			// If the user accepts, let's create a notification
			if ( permission === "granted" ) {
				var notification = new Notification( "TasmoAdmin" + title, { body: msg } );
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
				)
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


function getTemp( data, joinString ) {
	var temp       = [];
	var joinString = joinString || "<br/>";
	
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
				           data.StatusSNS.DS18x20.DS2.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
		if ( data.StatusSNS.DS18x20.DS3 !== undefined ) {
			temp.push( (
				           data.StatusSNS.DS18x20.DS3.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
		if ( data.StatusSNS.DS18x20.DS4 !== undefined ) {
			temp.push( (
				           data.StatusSNS.DS18x20.DS4.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
		if ( data.StatusSNS.DS18x20.DS5 !== undefined ) {
			temp.push( (
				           data.StatusSNS.DS18x20.DS5.Temperature + "°" + data.StatusSNS.TempUnit
			           ) );
		}
	}
	
	//6.1.1c 20180904
	if ( data.StatusSNS[ "DS18B20-1" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "DS18B20-1" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS[ "DS18B20-2" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "DS18B20-2" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS[ "DS18B20-3" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "DS18B20-3" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS[ "DS18B20-4" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "DS18B20-4" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	if ( data.StatusSNS[ "DS18B20-5" ] !== undefined ) {
		temp.push( (
			           data.StatusSNS[ "DS18B20-5" ].Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
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
	if ( data.StatusSNS.BME680 !== undefined ) {
		temp.push( (
			           data.StatusSNS.BME680.Temperature + "°" + data.StatusSNS.TempUnit
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
	if ( data.StatusSNS.BMP180 !== undefined ) {
		temp.push( (
			           data.StatusSNS.BMP180.Temperature + "°" + data.StatusSNS.TempUnit
		           ) );
	}
	
	//console.log( temp );
	
	return temp.join( joinString );
}

function getHumidity( data, joinString ) {
	var humi       = [];
	var joinString = joinString || "<br/>";
	
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
	if ( data.StatusSNS.BME680 !== undefined ) {
		if ( data.StatusSNS.BME680.Humidity !== undefined ) {
			humi.push( data.StatusSNS.BME680.Humidity + "%" );
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
	
	return humi.join( joinString );
}

function getPressure( data, joinString ) {
	var press      = [];
	var joinString = joinString || "<br/>";
	
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
	if ( data.StatusSNS.BME680 !== undefined ) {
		if ( data.StatusSNS.BME680.Pressure !== undefined ) {
			press.push( data.StatusSNS.BME680.Pressure + "&nbsp;hPa" );
		}
	}
	if ( data.StatusSNS.BMP180 !== undefined ) {
		if ( data.StatusSNS.BMP180.Pressure !== undefined ) {
			press.push( data.StatusSNS.BMP180.Pressure + "&nbsp;hPa" );
		}
	}
	
	//console.log( press );
	
	return press.join( joinString );
}


function getSeaPressure( data, joinString ) {
	var press      = [];
	var joinString = joinString || "<br/>";
	
	if ( data.StatusSNS.BMP180 !== undefined ) {
		if ( data.StatusSNS.BMP180.SeaPressure !== undefined ) {
			press.push( data.StatusSNS.BMP180.SeaPressure + "&nbsp;hPa" );
		}
	}
	
	//console.log( press );
	
	return press.join( joinString );
}

function getDistance( data, joinString ) {
	var dist       = [];
	var joinString = joinString || "<br/>";
	
	if ( data.StatusSNS.SR04 !== undefined ) {
		if ( data.StatusSNS.SR04.Distance !== undefined ) {
			dist.push( data.StatusSNS.SR04.Distance + "cm" );
		}
	}
	
	//console.log( press );
	
	return dist.join( joinString );
}


function getEnergyPower( data, joinString ) {
	var enerygPower = [];
	var joinString  = joinString || "<br/>";
	
	if ( data.StatusSNS.ENERGY !== undefined ) {
		if ( data.StatusSNS.ENERGY.Power !== undefined ) {
			enerygPower.push( data.StatusSNS.ENERGY.Power + "W" );
		}
		
		if ( data.StatusSNS.ENERGY.Today !== undefined ) {
			var tmpString = data.StatusSNS.ENERGY.Today;
			if ( data.StatusSNS.ENERGY.Yesterday !== undefined ) {
				tmpString += "/" + data.StatusSNS.ENERGY.Yesterday;
			}
			enerygPower.push( tmpString + "kWh" );
		}
		
		if ( data.StatusSNS.ENERGY.Current !== undefined ) {
			enerygPower.push( data.StatusSNS.ENERGY.Current + "A" );
		}
	}
	//console.log( press );
	
	return enerygPower.join( joinString );
}

//function getEnergyTodayYesterday( data ) {
//	var energyTodayYesterday = [];
//
//	if ( data.StatusSNS.ENERGY !== undefined ) {
//		if ( data.StatusSNS.ENERGY.Today !== undefined ) {
//			var tmpString = data.StatusSNS.ENERGY.Today;
//			if ( data.StatusSNS.ENERGY.Yesterday !== undefined ) {
//				tmpString += "/" + data.StatusSNS.ENERGY.Today;
//			}
//			energyTodayYesterday.push( tmpString + "kWh" );
//		}
//	}
//
//	//console.log( press );
//
//	return energyTodayYesterday.join( joinString );
//}


Date.prototype.addHours = function ( h ) {
	this.setHours( this.getHours() + h );
	return this;
};

function getGas( data, joinString ) {
	var gas        = [];
	var joinString = joinString || "<br/>";
	
	if ( data.StatusSNS.BME680 !== undefined ) {
		if ( data.StatusSNS.BME680.Gas !== undefined ) {
			gas.push( data.StatusSNS.BME680.Gas + "kOhm" );
		}
	}
	
	//console.log( press );
	
	return gas.join( joinString );
}

function checkNightmode( config ) {
	console.log( "[APP][checkNightmode] Start" );
	var config = config || "auto";
	
	var currentTime = new Date();
	var hour        = currentTime.getHours();
	
	//console.log( "check Nightmode => " + hour + "h - " + config );
	
	
	if ( config === "disable" ) {
		$( "body" ).removeClass( "nightmode" );
		console.log( "[APP][checkNightmode] disabled" );
	} else {
		if ( config === "auto" ) {
			console.log( "[APP][checkNightmode] check time" );
			if ( hour >= 18 || hour <= 8 ) {   //@TODO: get sunrise by geo
				$( "body" ).addClass( "nightmode" );
				console.log( "[APP][checkNightmode] its night" );
			} else {
				$( "body" ).removeClass( "nightmode" );
				console.log( "[APP][checkNightmode] its day" );
			}
			
			
			setTimeout( function () {
				checkNightmode( config );
			}, 15 * 60 * 1000 );
			
		} else if ( config === "always" ) {
			console.log( "[APP][checkNightmode] always" );
			$( "body" ).addClass( "nightmode" );
		}
	}
	if ( $( "body" ).hasClass( "nightmode" ) ) {
		nightmode = true;
	}
}

function checkForUpdate( timer ) {
	if ( $( "#versionHolder" ).data( "update-check" ) == "0" ) {
		console.log( "[APP][checkForUpdate] Update check is disabed" );
		$( "#update-icon" ).remove();
		return;
	}
	console.log( "[APP][checkForUpdate] Start" );
	var timer         = timer || true;
	var icon          = $( "#update-icon" );
	var currentGitTag = icon.data( "current_git_tag" );
	
	if ( icon.parent().hasClass( "update-now" ) ) {
		console.log( "[APP][checkForUpdate] NEW VERSION FOUND ALREADY" );
		return true;
	}
	
	if ( icon.hasClass( "fa-spin" ) ) {
		console.log( "[APP][checkForUpdate] Still searching" );
		return false;
	}
	
	icon.removeClass( "fa-check" )
	    .removeClass( "fa-question" )
	    .removeClass( "fa-times" )
	    .addClass( "fa-sync" )
	    .addClass( "fa-spin" );
	
	var action = "releases/latest";
	if ( currentGitTag.indexOf( "beta" ) !== false ) {
		action = "releases";
	}
	var githubApiRelease = "https://api.github.com/repos/reloxx13/TasmoAdmin/" + action;
	
	$.get( githubApiRelease, {}, function ( result ) {
		if ( result !== undefined ) {
			if ( Array.isArray( result ) ) {
				result = result[ 0 ];
			}
			if ( result.tag_name !== undefined ) {
				var latestTag = result.tag_name;
				console.log( "[APP][checkForUpdate] latestTag => " + latestTag );
				if ( latestTag != currentGitTag ) {
					console.log( "[APP][checkForUpdate] NEW VERSION FOUND" );
					if ( result.assets.length !== 3 ) {
						console.log( "[APP][checkForUpdate] Seems like Travis is not done yet" );
						icon.removeClass( "fa-sync" ).addClass( "fa-check" );
						if ( timer ) {
							setTimeout( checkForUpdate, 5 * 60 * 1000 );
						}
					} else {
						icon.removeClass( "fa-sync" )
						    .removeClass( "fa-spin" )
						    .addClass( "fa-cloud-download-alt" )
						    .parent()
						    .addClass(
							    "update-now" );
					}
				} else {
					console.log( "[APP][checkForUpdate] No update found" );
					icon.removeClass( "fa-sync" ).addClass( "fa-check" );
					if ( timer ) {
						setTimeout( checkForUpdate, 15 * 60 * 1000 );
					}
				}
			} else {
				if ( result.message !== undefined ) {
					icon.removeClass( "fa-sync" ).removeClass( "fa-spin" ).addClass( "fa-times" );
					console.log( "[APP][checkForUpdate] Github Error => " + result.message );
					setTimeout( checkForUpdate, 30 * 60 * 1000 );
				}
			}
		}
		
		
		icon.removeClass( "fa-spin" );
		
	}, "json" ).fail( function ( result ) {
		icon.removeClass( "fa-sync" ).removeClass( "fa-spin" ).addClass( "fa-times" );
		//console.log( result );
		console.log( "[APP][checkForUpdate] Github Error => " + result.status + ": " + result.responseJSON.message );
		setTimeout( checkForUpdate, 30 * 60 * 1000 );
	} );
	
}


jQuery.fn.shake = function ( intShakes, intDistance, intDuration ) {
	this.each( function () {
		$( this ).css( "position", "relative" );
		for ( var x = 1; x <= intShakes; x++ ) {
			$( this ).animate(
				{
					left: (
						intDistance * -1
					)
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

