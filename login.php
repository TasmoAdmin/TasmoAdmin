<?php

include_once( "./includes/top.php" );


$Config = new Config();

session_start();


$register = FALSE;
$msg      = FALSE;
$user     = $Config->read( "username" );
$password = $Config->read( "password" );

if( isset( $_GET[ "logout" ] ) ) {
	ob_start();
	
	session_unset();
	session_destroy();
	
	header( "Location: login.php" );
	ob_end_flush();
}

if( isset( $_POST ) && !empty( $_POST ) ) {
	if( isset( $_POST[ "register" ] ) && ( $user == "" || $password == "" ) ) {
		$Config->write( "username", $_POST[ "username" ] );
		$Config->write( "password", md5( $_POST[ "password" ] ) );
		$_SESSION[ 'login' ] = "1";
		header( "Location: " . _APPROOT_ . "index.php" );
		
	} else if( isset( $_POST[ "login" ] ) ) {
		if( $user == $_POST[ "username" ] && $password == md5( $_POST[ "password" ] ) ) {
			$_SESSION[ 'login' ] = "1";
			header( "Location: " . _APPROOT_ . "index.php" );
		} else {
			$msg = "Logindaten nicht korrekt!";
		}
	}
}

if( $user == "" || $password == "" ) {
	$register = TRUE;
}


?>


<?php include_once( _INCLUDESDIR_ . "header.php" ); //always load header?>
<div id='login' class='center'>
    <p><?php echo $msg ? $msg : ""; ?></p>
    <form name='loginform' method='post'>
        <table id='' class='center-table' border='0' cellspacing='0'>

            <tr>
                <td>
                    <br/><input type='text' name='username' required placeholder='Username'><br/><br/>
                </td>
            </tr>
            <tr>
                <td>
                    <br/><input type='password' name='password' required placeholder='Password'><br/><br/>
                </td>
            </tr>
            <tr>
                <td>
                    <br/>
                    <button type='submit' name='<?php echo $register ? "register" : "login"; ?>' class='btn'>
						<?php echo $register ? "Registrieren" : "Login"; ?>
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php include_once( _INCLUDESDIR_ . "footer.php" ); //always load header?>

