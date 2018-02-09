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
		timeout: 10,
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
	
	this.updateConfig = function ( device_id, cmnd, newvalue, callback ) {
		var cmnd = cmnd + " " + newvalue;
		
		doAjax( null, device_id, cmnd, callback );
	};
	
	this.generic = function ( device_id, cmnd, newvalue, callback ) {
		var newvalue = newvalue !== undefined ? " " + newvalue : "";
		var cmnd     = cmnd + " " + newvalue;
		
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
		
	}
	
	;
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
			        data    : {
				        id  : id,
				        cmnd: encodeURIComponent( cmnd ),
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
			        },
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
				        target  : "csv",
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
			        },
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