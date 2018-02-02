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
		timeout: 3,
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
				
				        console.log( "[Sonoff][doAjax][" + ip + "] Response from: " + cmnd + " => " + JSON.stringify(
					        data ) );
				        if ( data.WARNING ) {
					        alert( ip + ": " + data.WARNING );
				        }
				
				        callback( data );
				
			        },
			        error   : function ( data, xmlhttprequest, textstatus, message ) {
				        callback( data );
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