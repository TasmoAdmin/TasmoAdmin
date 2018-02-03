$( document ).on( "ready", function() {
    deviceTools();
    updateStatus();
} );


function updateStatus() {
    $( '#content .box_device' ).each( function( key, box ) {

        var device_ip     = $( box ).data( "device_ip" );
        var device_id     = $( box ).data( "device_id" );
        var device_relais = $( box ).data( "device_relais" );
        var device_group  = $( box ).data( "device_group" );

        if( !$( box ).hasClass( "updating" ) ) {
            $( box ).addClass( "updating" );

            console.log( "[Start][updateStatus]get status from " + $( box ).data( "device_ip" ) );

            if( device_group == "multi" && device_relais > 1 ) {
                console.log( "[Start][updateStatus]skip multi " + $( box ).data( "device_ip" ) );
                return; //relais 1 will update all others
            }


            Sonoff.getStatus( device_ip, device_id, device_relais, function( data ) {

                                  if( data && !data.ERROR && !data.WARNING ) {

                                      if( device_group == "multi" ) {
                                          $( '#content .box_device[data-device_group="multi"][data-device_ip="' + device_ip + '"]' )
                                              .each( function( key, groupbox ) {
                                                  var img           = $( groupbox ).find( "img" );
                                                  var src           = _RESOURCESDIR_ + "img/device_icons/"
                                                                      + img.data( "icon" )
                                                                      + "_%pw.png";
                                                  var device_status = eval( "data.StatusSTS.POWER" + $( groupbox )
                                                      .data( "device_relais" ) );

                                                  console.log( device_status.toLowerCase() );
                                                  src = src.replace( "%pw", device_status.toLowerCase() );
                                                  img.attr( "src", src ).parent().removeClass( "animated" );
                                                  updateBox( $( groupbox ), data, device_status );
                                                  $( groupbox ).removeClass( "error" ).find( ".animated" ).removeClass( "animated" );
                                                  $( groupbox ).removeClass( "updating" );
                                              } );
                                      } else {
                                          var img           = $( box ).find( "img" );
                                          var src           = _RESOURCESDIR_ + "img/device_icons/"
                                                              + img.data( "icon" )
                                                              + "_%pw.png";
                                          var device_status = data.StatusSTS.POWER || data.StatusSTS.POWER1;

                                          src = src.replace( "%pw", device_status.toLowerCase() );
                                          img.attr( "src", src ).parent().removeClass( "animated" );
                                          updateBox( $( box ), data, device_status );
                                          $( box ).removeClass( "error" ).find( ".animated" ).removeClass( "animated" );
                                          $( box ).removeClass( "updating" );
                                      }


                                  } else {
                                      console.log( "[Start][updateStatus]ERROR "
                                                   + device_ip
                                                   + " => "
                                                   + data.ERROR
                                                   || "Unknown Error" );
                                      if( device_group == "multi" ) {
                                          $( '#device-list tbody tr[data-device_group="multi"][data-device_ip="' + device_ip + '"]' )
                                              .each( function( key, groupbox ) {
                                                  $( groupbox ).addClass( "error" ).find( ".animated" ).removeClass( "animated" );
                                                  $( groupbox ).removeClass( "updating" );
                                              } );
                                      } else {
                                          $( box ).addClass( "error" ).find( ".animated" ).removeClass( "animated" );
                                          $( box ).removeClass( "updating" );
                                      }
                                  }
                                  //console.log( result );

                              }
            );
        }
    } );


    if( refreshtime ) {
        console.log( "[Global][Refreshtime]" + refreshtime + "ms" );
        setTimeout( function() {
            updateStatus();
        }, refreshtime );
    } else {
        console.log( "[Global][Refreshtime]Dont refresh" );
    }

};

function deviceTools() {
    $( '#content .box_device' ).on( "click", function( e ) {
        e.preventDefault();
        var device_box = $( this );
        device_box.find( "img" ).effect( "shake", { distance : 3 } );
        var device_ip     = device_box.data( "device_ip" );
        var device_id     = device_box.data( "device_id" );
        var device_relais = device_box.data( "device_relais" );
        Sonoff.toggle( device_ip, device_id, device_relais, function( data ) {
            if( data && !data.ERROR && !data.WARNING ) {
                var img           = device_box.find( "img" );
                var src           = _RESOURCESDIR_ + "img/device_icons/" + img.data( "icon" ) + "_%pw.png";
                var device_status = data.POWER || eval( "data.POWER" + device_relais );
                src               = src.replace( "%pw", device_status.toLowerCase() );
                img.attr( "src", src ).parent().removeClass( "animated" );
                device_box.removeClass( "error" );
            } else {
                device_box.addClass( "error" );
                console.log( "[Start][toggle]ERROR "
                             + device_ip
                             + " => "
                             + data.ERROR
                             || "Unknown Error" );
            }
        } );


    } );
}


function updateBox( row, data, device_status ) {

    var version = parseVersion( data.StatusFWR.Version );
    console.log( "version => " + version );

    if( version >= 510009 ) {//no json translations since 5.10.0j
        var rssi   = data.StatusSTS.Wifi.RSSI;
        var ssid   = data.StatusSTS.Wifi.SSId;
        var uptime = data.StatusSTS.Uptime;
    } else { //try german else use english
        var rssi   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI;
        var ssid   = data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId;
        var uptime = data.StatusSTS.Laufzeit != "undefined" ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime;
        //console.log( uptime );
    }

    var temp = getTemp( data );

    if( temp != "" ) {
        $( row ).find( ".temp span" ).html( temp ).parent().removeClass( "hidden" );
    }
    var humidity = getHumidity( data );

    if( humidity != "" ) {
        $( row ).find( ".humidity span" ).html( humidity ).parent().removeClass( "hidden" );
    }

    var idx = (
        data.idx ? data.idx : ""
    );
    if( idx != "" ) {
        $( row ).find( ".idx span" ).html( idx );
        $( "#device-list .idx" ).removeClass( "hidden" ).show();
    }

    $( row ).find( ".version span" ).html( data.StatusFWR.Version );

    if( device_status == "ON" ) {
        $( row ).find( ".status" ).find( "input" ).prop( "checked", "checked" ).parent().removeClass( "error" );
    } else {
        $( row ).find( ".status" ).find( "input" ).removeProp( "checked" ).parent().removeClass( "error" );
    }
    $( row ).find( ".rssi span" ).html( rssi + "%" ).attr( "title", ssid );
    $( row ).find( ".runtime span" ).html( "~" + uptime + "h" );


    //MORE
    $( row ).find( ".hostname span" ).html( data.StatusNET.Hostname !== undefined ? data.StatusNET.Hostname : "?" );
    $( row ).find( ".mac span" ).html( data.StatusNET.Mac !== undefined ? data.StatusNET.Mac : "?" );
    $( row ).find( ".mqtt span" ).html( data.StatusMQT !== undefined ? "1" : "0" );
    $( row ).find( ".poweronstate span" ).html( data.Status.PowerOnState
                                                !== undefined
                                                    ? data.Status.PowerOnState
                                                    : "?" );
    $( row ).find( ".ledstate span" ).html( data.Status.LedState !== undefined ? data.Status.LedState : "?" );
    $( row ).find( ".savedata span" ).html( data.Status.SaveData !== undefined ? data.Status.SaveData : "?" );
    $( row ).find( ".sleep span" ).html( data.StatusPRM.Sleep !== undefined ? data.StatusPRM.Sleep + "ms" : "?" );
    $( row ).find( ".bootcount span" ).html( data.StatusPRM.BootCount !== undefined ? data.StatusPRM.BootCount : "?" );
    $( row ).find( ".savecount span" ).html( data.StatusPRM.SaveCount !== undefined ? data.StatusPRM.SaveCount : "?" );
    $( row ).find( ".log span" ).html( (
                                           data.StatusLOG.SerialLog !== undefined ? data.StatusLOG.SerialLog : "?"
                                       )
                                       + "|"
                                       + (
                                           data.StatusLOG.WebLog !== undefined ? data.StatusLOG.WebLog : "?"
                                       )
                                       + "|"
                                       + (
                                           data.StatusLOG.SysLog !== undefined ? data.StatusLOG.SysLog : "?"
                                       ) );
    $( row ).find( ".wificonfig span" ).html( data.StatusNET.WifiConfig
                                              !== undefined
                                                  ? data.StatusNET.WifiConfig
                                                  : "?" );
    $( row ).find( ".vcc span" ).html( data.StatusSTS.Vcc !== undefined ? data.StatusSTS.Vcc + "V" : "?" );


    $( row ).removeClass( "updating" );
}