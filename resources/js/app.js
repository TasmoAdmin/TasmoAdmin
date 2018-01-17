var Sonoff;
var refreshtime = false;
$( document ).on( "ready", function() {
    /**
     * Sonoff Handler
     * @type {Sonoff}
     */
    Sonoff = new Sonoff( { timeout : 8 } );


    var appendLoading = function( elem, replace ) {
        var replace = replace || false;
        var loader  = $( '<div>', { class : "loader" } ).append( $( 'img', { src : "./resources/img/loading.gif" } ) );

        if( replace ) {
            $( elem ).html( loader );
        } else {
            $( elem ).append( loader );
        }
    };

    $( '.hamburger' ).click( function() {
        $( "#navi" ).toggleClass( "show" );
        $( '.hamburger' ).toggleClass( "open" );
    } );

    if( $( "#content" ).data( "refreshtime" ) !== "none" ) {
        refreshtime = $( "#content" ).data( "refreshtime" ) * 1000;
    }

    $( "input[type=\"number\"]" ).keydown( function( e ) {
        // Allow: backspace, delete, tab, escape, enter and .
        if( $.inArray( e.keyCode, [ 46, 8, 9, 27, 13, 110, 190 ] ) !== -1 ||
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
        if( (
                e.shiftKey || (
                e.keyCode < 48 || e.keyCode > 57
                )
            ) && (
                e.keyCode < 96 || e.keyCode > 105
            ) ) {
            e.preventDefault();
        }
    } );


    $( "#language-switch" ).on( "change", function( e ) {

        var optionSelected = $( "option:selected", this );
        var valueSelected  = this.value;

        var curUrl = window.location.toString();
        curUrl     = curUrl.replace( /[\?\&]lang=[a-z]*/g, "" );
        
        window.location.href = curUrl + (
            curUrl.indexOf( "?" ) !== -1 ? "&" : "?"
        ) + "lang=" + valueSelected;
    } );

} );


function notifyMe( msg, title ) {
    var title = title || "";
    if( title != "" ) {
        title = " - " + title;
    }

    var icon = "./resources/img/favicons/apple-icon-180x180.png";

    // Let's check if the browser supports notifications
    if( !(
            "Notification" in window
        ) ) {
        return;
    }


    // Let's check whether notification permissions have already been granted
    else if( Notification.permission === "granted" ) {
        // If it's okay let's create a notification
        var notification = new Notification( "SonWEB" + title, { body : msg, icon : icon } );
        setTimeout( notification.close.bind( notification ), 3000 );
    }

    // Otherwise, we need to ask the user for permission
    else if( Notification.permission !== 'denied' ) {
        Notification.requestPermission( function( permission ) {
            // If the user accepts, let's create a notification
            if( permission === "granted" ) {
                var notification = new Notification( "SonWEB" + title, { body : msg } );
                setTimeout( notification.close.bind( notification ), 3000 );
            }
        } );
    }

    // Finally, if the user has denied notifications and you
    // want to be respectful there is no need to bother them any more.
}
