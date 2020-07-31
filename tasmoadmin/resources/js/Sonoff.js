/**
 * Your classic Sonoff
 * @typedef {Object} Sonoff
 * @method getStatus
 * @property {int} timeout Current state of the Sonoff
 */

var Sonoff = function ( options ) {
	
	/*
	 * Variables accessible
	 * in the class
	 */
	var vars = {
		timeout: 10
	};
	
	/*
	 * Can access this.method
	 * inside other methods using
	 * root.method()
	 */
	var root = this;
	
	/*
	 * Constructor
	 */
	this.construct = function ( options ) {
		$.extend( vars, options );
	};
	
	/**
	 * getStatus
	 *
	 * @param {string} ip
	 * @param {int} id
	 * @param {int} relais
	 * @param {function} callback
	 */
	
	
	this.getStatus = function ( ip, id, relais, callback, params ) {
		relais   = relais || 1;
		var cmnd = "Status 0";
		
		doAjax( ip, id, cmnd, callback, params );
	};
	
	this.getAllStatus = function ( timeout, callback ) {
		var cmnd = "Status 0";
		
		doAjaxAll( timeout, cmnd, callback );
	};
	
	this.updateConfig = function ( device_id, cmnd, newvalue, callback ) {
		var cmnd = cmnd + " " + newvalue;
		
		doAjax( null, device_id, cmnd, callback );
	};
	
	this.generic = function ( device_id, cmnd, newvalue, callback ) {
		var newvalue = (
			(
				newvalue !== undefined
			) ? " " + newvalue : ""
		);
		var cmnd     = cmnd + newvalue;
		
		doAjax( null, device_id, cmnd, callback );
	};
	/**
	 * getStatus
	 *
	 * @param {string} ip
	 * @param {int} id
	 * @param {int} relais
	 * @param {function} callback
	 */
	this.toggle = function ( ip, id, relais, callback ) {
		relais   = relais || 1;
		var cmnd = "Power" + relais + " toggle";
		
		console.log( "[Sonoff][toggle][" + ip + "][Relais" + relais + "] cmnd => " + cmnd );
		
		doAjax( ip, id, cmnd, callback );
		
	};
	
	
	/**
	 * getStatus
	 *
	 * @param {string} ip
	 * @param {int} id
	 * @param {int} relais
	 * @param {function} callback
	 */
	this.off = function ( ip, id, relais, callback ) {
		relais   = relais || 1;
		var cmnd = "Power" + relais + " 0";
		
		console.log( "[Sonoff][toggle][" + ip + "][Relais" + relais + "] cmnd => " + cmnd );
		
		doAjax( ip, id, cmnd, callback );
		
	};
	
	/*
	 * Private method
	 * Can only be called inside class
	 */
	var doAjax = function ( ip, id, cmnd, callback ) {
		//var url = root.buildCmndUrl( ip, cmnd );
		var ip = ip || id;
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjax",
			        timeout : options.timeout * 1000,
			        cache   : false,
			        type    : "post",
			        async   : true,
			        data    : {
				        id  : id,
				        cmnd: encodeURIComponent( cmnd )
			        },
			        success : function ( data ) {
				        // var data = data || { ERROR : "NO DATA" };
				
				        //console.log( "[Sonoff][doAjax][" + ip + "] Response from: " + cmnd + " => " + JSON.stringify(
				        //   data ) );
				        console.log( "[Sonoff][doAjax][" + ip + "] Got response from: " + cmnd );
				
				        if ( data.WARNING ) {
					        alert( ip + ": " + data.WARNING );
				        }
				        if ( callback !== undefined ) {
					        callback( data );
				        }
			        },
			        error   : function ( data, xmlhttprequest, textstatus, message ) {
				        if ( callback !== undefined ) {
					        callback( data );
				        }
			        }
		        } );
	};
	/*
	 * Private method
	 * Can only be called inside class
	 */
	var doAjaxAll = function ( timeout, cmnd, callback ) {
		//var url = root.buildCmndUrl( ip, cmnd );
		var timeout = timeout || options.timeout;
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjaxAll",
			        timeout : timeout * 1000,
			        cache   : false,
			        type    : "post",
			        data    : {
				        cmnd: encodeURIComponent( cmnd )
			        },
			        success : function ( data ) {
				        // var data = data || { ERROR : "NO DATA" };
				
				        //console.log( "[Sonoff][doAjax][" + ip + "] Response from: " + cmnd + " => " + JSON.stringify(
				        //   data ) );
				        console.log( "[Sonoff][doAjaxAll] Got response from: " + cmnd );
				
				
				        if ( data.WARNING ) {
					        alert( ip + ": " + data.WARNING );
				        }
				        if ( callback !== undefined ) {
					        callback( data );
				        }
			        },
			        error   : function ( data, xmlhttprequest, textstatus, message ) {
				        if ( callback !== undefined ) {
					        callback( data );
				        }
			        }
		        } );
	};
	
	
	this.parseDeviceStatus = function ( data, device_relais ) {
		var device_status = "NONE";
		
		if ( data.StatusSTS !== undefined ) {
			if ( device_relais !== undefined && eval( "data.StatusSTS.POWER" + device_relais ) !== undefined ) {
				
				if ( eval( "data.StatusSTS.POWER" + device_relais + ".STATE" ) !== undefined ) {
					device_status = eval( "data.StatusSTS.POWER" + device_relais + ".STATE" );
				} else {
					device_status = eval( "data.StatusSTS.POWER" + device_relais );
				}
			} else {
				if ( data.StatusSTS.POWER !== undefined ) {
					if ( data.StatusSTS.POWER.STATE !== undefined ) {
						device_status = data.StatusSTS.POWER.STATE;
					} else {
						device_status = data.StatusSTS.POWER;
					}
				}
			}
		} else {
			if ( device_relais !== undefined && eval( "data.POWER" + device_relais ) !== undefined ) {
				
				if ( eval( "data.POWER" + device_relais + ".STATE" ) !== undefined ) {
					device_status = eval( "data.POWER" + device_relais + ".STATE" );
				} else {
					device_status = eval( "data.POWER" + device_relais );
				}
			} else {
				if ( data.POWER !== undefined ) {
					if ( data.POWER.STATE !== undefined ) {
						device_status = data.POWER.STATE;
					} else {
						device_status = data.POWER;
					}
				}
			}
		}
		
		return device_status;
	};
	
	
	this.directAjax = function ( url ) {
		//var url = root.buildCmndUrl( ip, cmnd );
		$.ajax( {
			        url    : url,
			        timeout: options.timeout * 1000,
			        cache  : false,
			        success: function ( data ) {
				
			        },
			        error  : function ( data, xmlhttprequest, textstatus, message ) {
				
			        }
		        } );
	};
	
	this.setDeviceValue = function ( id, field, newvalue, td ) {
		$.ajax( {
			        dataType: "json",
			        url     : "index.php?doAjax",
			        timeout : options.timeout * 1000,
			        cache   : false,
			        type    : "post",
			        data    : {
				        id      : id,
				        field   : encodeURIComponent( field ),
				        newvalue: encodeURIComponent( newvalue ),
				        target  : "csv"
			        },
			        success : function ( data ) {
				        // var data = data || { ERROR : "NO DATA" };
				
				        console.log( "[Sonoff][doAjax][" + id + "] Response from: " + field + " => " + JSON.stringify(
					        data ) );
				        console.log( "[Sonoff][doAjax][" + id + "] Got response from: " + field + " => " + newvalue );
				
				        td.html( data.position );
				
				
			        },
			        error   : function ( data, xmlhttprequest, textstatus, message ) {
				        console.log( "ERROR setDeviceValue" );
			        }
		        } );
	};
	
	this.buildCmndUrl = function ( ip, cmnd ) {
		cmnd    = cmnd.replace( " ", "%20" );
		var url = "http://" + ip + "/cm?cmnd=" + cmnd;
		url     = encodeURIComponent( url );
		return url;
	};
	
	
	/*
	 * Pass options when class instantiated
	 */
	this.construct( options );
	
};