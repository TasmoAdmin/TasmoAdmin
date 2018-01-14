<?php
	header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
	header( "Pragma: no-cache" );
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/html">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta http-equiv="Cache-control" content="public">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	
	<link rel="apple-touch-icon" sizes="57x57" href="/resources/img/favicons/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/resources/img/favicons/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/resources/img/favicons/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/resources/img/favicons/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/resources/img/favicons/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/resources/img/favicons/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/resources/img/favicons/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/resources/img/favicons/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/resources/img/favicons/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/resources/img/favicons/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/resources/img/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/resources/img/favicons/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/resources/img/favicons/favicon-16x16.png">
	<link rel="manifest" href="/resources/img/favicons/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/resources/img/favicons/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
	
	
	<title><?php echo isset( $title ) ? $title." - " : ""; ?>SonWEB</title>
	<script src="/resources/js/jquery-ui/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
	<script src="/resources/js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
	
	<script type='text/javascript' src='/resources/js/Sonoff.js?<?php echo time(); ?>'></script>
	
	<script type='text/javascript' src='/resources/js/app.js?<?php echo time(); ?>'></script>
	
	<link href="/resources/js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.css" rel="stylesheet">
	<link href='/resources/css/animate.css?<?php echo time(); ?>' rel='stylesheet'>
	<link href='/resources/css/style.css?<?php echo time(); ?>' rel='stylesheet'>

</head>
<body>

<div id='header'>
	<h1><a href='/index.php?page=start'>SonWEB</a></h1>
</div>