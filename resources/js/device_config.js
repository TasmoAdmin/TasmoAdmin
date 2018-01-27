$( document ).on( "ready", function () {
	var activeTabIndex = $( "#config_tabs li.active" ).data( "tab-index" );
	$( "#config_tabs" ).tabs( { active: activeTabIndex } );
} );