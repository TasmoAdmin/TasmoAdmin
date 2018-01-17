<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Cache-control" content="no-cache, must-revalidate"/>
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"
          href="<?php echo _RESOURCESDIR_; ?>img/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo _RESOURCESDIR_; ?>img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo _RESOURCESDIR_; ?>img/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo _RESOURCESDIR_; ?>img/favicons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo _RESOURCESDIR_; ?>img/favicons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo _RESOURCESDIR_; ?>img/favicons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <title><?php echo isset( $title ) ? $title . " - " : ""; ?>SonWEB</title>
    <script src="<?php echo _RESOURCESDIR_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="<?php echo _RESOURCESDIR_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.js"></script>

    <script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/Sonoff.js?<?php echo time(); ?>'></script>

    <script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/app.js?<?php echo time(); ?>'></script>

    <link href='<?php echo _RESOURCESDIR_; ?>js/jquery-ui/jquery-ui-1.12.1.custom/jquery-ui.css' rel='stylesheet'>
    <link href='<?php echo _RESOURCESDIR_; ?>css/animate.css?<?php echo time(); ?>' rel='stylesheet'>
    <link href='<?php echo _RESOURCESDIR_; ?>css/style.css?<?php echo time(); ?>' rel='stylesheet'>
    <link href='<?php echo _RESOURCESDIR_; ?>css/mobile.css?<?php echo time(); ?>' rel='stylesheet'>

</head>
<body>

<div id='header'>

    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <h1><a href='<?php echo _APPROOT_; ?>index.php?page=start'>SonWEB <?php echo isset( $title ) ? " -&nbsp;" . $title
				: ""; ?> </a></h1>

    <div class='language-switch-holder'>
        <select name='language-switch' id='language-switch'>
            <option value='de' <?php echo $lang == "de" ? "selected=\"selected\"" : ""; ?>>DE</option>
            <option value='en' <?php echo $lang == "en" ? "selected=\"selected\"" : ""; ?>>EN</option>
            <option value='es' <?php echo $lang == "es" ? "selected=\"selected\"" : ""; ?>>ES</option>
            <option value='fr' <?php echo $lang == "fr" ? "selected=\"selected\"" : ""; ?>>FR</option>
            <option value='nl' <?php echo $lang == "nl" ? "selected=\"selected\"" : ""; ?>>NL</option>
            <option value='pl' <?php echo $lang == "pl" ? "selected=\"selected\"" : ""; ?>>PL</option>
            <option value='ru' <?php echo $lang == "ru" ? "selected=\"selected\"" : ""; ?>>RU</option>
        </select>
    </div>
</div>