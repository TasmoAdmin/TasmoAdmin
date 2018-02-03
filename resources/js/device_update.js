var ajaxTimeout = 8;
$( document ).on( "ready", function() {
    // console.log( device_ids );
    device_ids = $.parseJSON( device_ids );

    var progressBox = $( "#progressbox" );


    log( "", "", $.i18n( "BLOCK_GLOBAL" ), $.i18n( "BLOCK_GLOBAL_START" ), "success" );

    $.each( device_ids, step1 );


    function step1( index, id ) {
        log( id, 1, $.i18n( "BLOCK_GLOBAL" ), $.i18n( "BLOCK_GLOBAL_START_STEP_1" ), "info" );
        device_responses( id, setOTAURL, id, "MINIMAL", 1 );
    }


    function device_responses( id, callback, p1, p2, step, tries ) {
        var tries = tries || 1;
        var cmnd  = "Status 2";
        log(
            id,
            step,
            $.i18n( 'BLOCK_CHECK_CONNECTION' ), $.i18n( 'BLOCK_CHECK_CONNECTION_START_CHECK_TRY' ) + tries,
            "info"
        );
        $.ajax( {
                    dataType : "json",
                    url      : _BASEURL_ + "doAjax",
                    data     : {
                        id   : id,
                        cmnd : cmnd,
                    },
                    timeout  : ajaxTimeout * 1000,
                    custom   : {
                        callback : callback,
                    },
                    type     : "post",
                    success  : function( data ) {
                        console.log( data );
                        if( data && !data.ERROR ) {
                            if( data.WARNING ) {
                                log(
                                    id,
                                    step,
                                    $.i18n( 'BLOCK_CHECK_CONNECTION' ), $.i18n(
                                    'BLOCK_CHECK_CONNECTION_CHECK_ERROR_MSG' )
                                                                        + data.WARNING,
                                    "error"
                                );
                            }
                            log(
                                id,
                                step,
                                $.i18n( 'BLOCK_CHECK_CONNECTION' ),
                                $.i18n( 'BLOCK_CHECK_CONNECTION_CHECK_OK_VERSION' )
                                + data.StatusFWR.Version, "success"
                            );
                            this.custom.callback( p1, p2, step );
                        } else {
                            log( id, step, $.i18n( 'BLOCK_CHECK_CONNECTION' ),
                                $.i18n( 'BLOCK_CHECK_CONNECTION_CHECK_NO_RESPONSE_MSG' ) + data.ERROR, "error"
                            );
                            if( tries < 3 ) {
                                tries = tries + 1;
                                device_responses( id, callback, p1, p2, step, tries );
                            }
                        }
                    },
                    error    : function( badData ) {
                        log(
                            id,
                            step,
                            $.i18n( 'BLOCK_CHECK_CONNECTION' ),
                            $.i18n( 'BLOCK_CHECK_CONNECTION_CHECK_NO_RESPONSE_MSG' ),
                            "error"
                        );
                        if( tries < 3 ) {
                            tries = tries + 1;
                            device_responses( id, callback, p1, p2, step, tries );
                        }
                    },
                } );
    }

    function setOTAURL( id, fwType, step ) {
        var fw = "";
        if( fwType === "MINIMAL" ) {
            fw = $( "#ota_minimal_firmware_url" ).val();
        } else {
            fw = $( "#ota_new_firmware_url" ).val();
        }

        log( id, step, $.i18n( "BLOCK_OTAURL" ), $.i18n( "BLOCK_OTAURL_SET_URL_FWTYPE" ) + fwType, "info" );
        log( id, step, $.i18n( "BLOCK_OTAURL" ), $.i18n( "BLOCK_OTAURL_SET_URL_FWURL" ) + fw, "info" );
        var cmnd = "OtaUrl " + fw;
        $.ajax( {
                    dataType : "json",
                    url      : _BASEURL_ + "doAjax",
                    data     : {
                        id   : id,
                        cmnd : cmnd,
                    },
                    timeout  : ajaxTimeout * 1000,
                    type     : "post",
                    success  : function( data ) {
                        console.log( data );
                        if( data.WARNING ) {
                            log(
                                id,
                                step,
                                $.i18n( "BLOCK_OTAURL" ), $.i18n( "BLOCK_OTAURL_ERROR_MSG" ) + data.WARNING,
                                "error"
                            );
                        }
                        log(
                            id,
                            1,
                            $.i18n( "BLOCK_OTAURL" ), $.i18n( "BLOCK_OTAURL_SUCCESS_FWTYPE" ) + fwType,
                            "success"
                        );
                        startUpdate( id, step );

                    },
                    error    : function( badData ) {
                        log(
                            id,
                            step,
                            $.i18n( "BLOCK_OTAURL" ),
                            $.i18n( "BLOCK_OTAURL_CHECK_NO_RESPONSE_MSG" ),
                            "error"
                        );

                    },
                } );
    }

    function startUpdate( id, step ) {
        log( id, step, $.i18n( "BLOCK_UPDATE" ), $.i18n( "BLOCK_UPDATE_START" ), "info" );
        var cmnd = "Upgrade 1";
        $.ajax( {
                    dataType : "json",
                    url      : _BASEURL_ + "doAjax",
                    data     : {
                        id   : id,
                        cmnd : cmnd,
                    },
                    type     : "post",
                    timeout  : ajaxTimeout * 1000,
                    success  : function( data ) {
                        console.log( data );
                        if( data && !data.ERROR ) {
                            if( data.WARNING ) {
                                log(
                                    id,
                                    step,
                                    $.i18n( "BLOCK_UPDATE" ), $.i18n( "BLOCK_UPDATE_ERROR_MSG" ) + data.WARNING,
                                    "error"
                                );
                            } else {
                                log( id, step, $.i18n( "BLOCK_UPDATE" ), $.i18n( "BLOCK_UPDATE_SUCCESS" ), "info" );
                            }
                            checkUpdateDone( id, step, 1 );
                        } else {
                            log(
                                id,
                                step,
                                $.i18n( "BLOCK_UPDATE" ),
                                $.i18n( "BLOCK_UPDATE_CHECK_NO_RESPONSE_MSG" ),
                                "error"
                            );
                        }

                    },
                    error    : function( badData ) {
                        log(
                            id,
                            step,
                            $.i18n( "BLOCK_UPDATE" ),
                            $.i18n( "BLOCK_UPDATE_CHECK_NO_RESPONSE_MSG" ),
                            "error"
                        );

                    },
                } );

    }

    function step2( id ) {
        log( id, 2, $.i18n( "BLOCK_GLOBAL" ), $.i18n( "BLOCK_GLOBAL_START_STEP_2" ), "info" );

        device_responses( id, setOTAURL, id, "NEW FW", 2 );
    }

    function checkUpdateDone( id, step, i ) {
        if( i > 48 ) {
            log( id, step, $.i18n( "BLOCK_CHECK_UPDATE" ), $.i18n( "BLOCK_CHECK_UPDATE_ERROR_X_MIN" ), "error" );
            return;
        }
        var sec = 60;
        if( i > 1 ) {
            sec = 30;
        }
        log( id, step, $.i18n( "BLOCK_CHECK_UPDATE" ), $.i18n( "BLOCK_CHECK_UPDATE_WAIT_X_SECONDS" ) + sec, "info" );
        setTimeout(
            function() {
                var cmnd = "Status 2";
                $.ajax( {
                            dataType : "json",
                            url      : _BASEURL_ + "doAjax",
                            data     : {
                                id   : id,
                                cmnd : cmnd,
                            },
                            timeout  : ajaxTimeout * 1000,
                            type     : "post",
                            success  : function( data ) {
                                console.log( data );
                                if( data && !data.ERROR ) {
                                    if( step == 1 ) {
                                        log(
                                            id,
                                            step,
                                            $.i18n( "BLOCK_CHECK_UPDATE" ),
                                            $.i18n( "BLOCK_CHECK_UPDATE_UPDATE_DONE" ),
                                            "success"
                                        );
                                        step2( id );
                                    } else {
                                        log(
                                            id,
                                            step,
                                            $.i18n( "BLOCK_CHECK_UPDATE_DONE" ),
                                            $.i18n( "BLOCK_CHECK_UPDATE_DONE_MESSAGE" ),
                                            "success"
                                        );
                                    }
                                } else {
                                    log(
                                        id,
                                        step,
                                        $.i18n( "BLOCK_CHECK_UPDATE" ),
                                        $.i18n( "BLOCK_CHECK_UPDATE_STILL_UPDATING" ),
                                        "info"
                                    );
                                    checkUpdateDone( id, step, i + 1 );
                                }
                            },
                            error    : function( badData ) {
                                log(
                                    id,
                                    step,
                                    $.i18n( "BLOCK_CHECK_UPDATE" ),
                                    $.i18n( "BLOCK_CHECK_UPDATE_STILL_UPDATING" ),
                                    "info"
                                );
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

        if( id !== "" ) {
            entry += "[" + $.i18n( "BLOCK_GLOBAL_ID" ) + "-" + id + "]";
        }
        if( step !== "" ) {
            entry += "[" + $.i18n( "BLOCK_GLOBAL_STEP" ) + "-" + step + "]";
        }
        if( block !== "" ) {
            entry += "[" + block + "]";
        }

        entry += " " + msg;

        progressBox.append( "<span class='" + level + "'>" + entry + "</span>" );
        $( "#content-holder" ).animate( { scrollTop : progressBox[ 0 ].scrollHeight }, 500 );
    }
} );