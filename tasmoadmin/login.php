<?php
ob_start();
include_once("./includes/top.php");


$Config = new Config();


$register = FALSE;
$msg      = FALSE;
$user     = $Config->read("username");
$password = $Config->read("password");
$title    = __("LOGIN", "PAGE_TITLES");
$page     = "login";
if (isset($_GET["logout"])) {
	ob_start();
	
	session_unset();
	session_destroy();
	if (isset($_COOKIE['MyConfig'])) {
		unset($_COOKIE['MyConfig']);
		setcookie('MyConfig', '', time() - 3600, '/'); // empty value and old timestamp
	}
	
	header("Location: " . _BASEURL_ . "login");
	ob_end_flush();
}

if ($Config->read("login") == 0) {
	header("Location: " . _BASEURL_ . "");
}

if (isset($_POST) && !empty($_POST)) {
	$home = $Config->read("homepage");
	if (isset($_REQUEST["register"]) && ($user == "" || $password == "")) {
		LoginHelper::register($_REQUEST["username"], $_REQUEST["password"]);
		$_SESSION['login'] = "1";
		header("Location: " . _BASEURL_ . $home);
		
	}
	elseif (isset($_REQUEST["login"])) {
		if ($user == $_REQUEST["username"] && LoginHelper::login($_REQUEST["password"], $password)) {
			$_SESSION['login'] = "1";
			header("Location: " . _BASEURL_ . $home);
		}
		else {
			$msg = __("LOGIN_INCORRECT", "LOGIN");
		}
	}
}

if (empty($user) || $user == "" || empty($password) || $password == "") {
	$register = TRUE;
}

?>


<?php include_once(_INCLUDESDIR_ . "header.php"); //always load header?>

<div class="container-fluid" id='content'>
	<div id='content-holder'>
		<div class="row mx-0">
			<div class="col col-md-12">
				<h2 class="text-center text-white mb-4"><?php echo $title; ?></h2>
				<div class="row">
					<div class="col col-md-6 mx-auto">
						<span class="anchor" id="formLogin"></span>
						<?php if (isset($msg) && $msg != ""): ?>
							<div class="alert alert-danger alert-dismissible fade show mb-5"
								 data-dismiss="alert"
								 role="alert"
							>
								<?php echo $msg; ?>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						<?php endif; ?>
						<!-- form card login -->
						<div class="card rounded-0 bg-dark text-white">
							<div class="card-body">
								<form class="form" name='loginform' method='POST'>
									<div class="form-group col">
										<label for="username">Username</label>
										<input type="text"
											   autofocus="autofocus"
											   class="form-control form-control-lg rounded-0"
											   name="username"
											   id="username"
											   placeholder='<?php echo __("LOGIN_USERNAME_PLACEHOLDER", "LOGIN"); ?>'
											   required=""
										>
									
									
									</div>
									<div class="form-group col">
										<label>Password</label>
										<input type="password"
											   class="form-control form-control-lg rounded-0"
											   id="password"
											   name="password"
											   required=""
											   placeholder='<?php echo __("LOGIN_PASSWORD_PLACEHOLDER", "LOGIN"); ?>'
										>
									</div>
									<div class='col col-12'>
										<button type='submit'
												name='<?php echo $register ? "register" : "login"; ?>'
												class='btn btn-success btn-lg float-right'
										>
											<?php echo $register
												? __("BTN_REGISTER", "LOGIN")
												: __(
													"BTN_LOGIN",
													"LOGIN"
												); ?>
										</button>
									</div>
								</form>
							</div>
							<!--/card-block-->
						</div>
						<!-- /form card login -->
					
					</div>
				
				
				</div>
				<!--/row-->
			
			</div>
			<!--/col-->
		</div>
		<!--/row-->
	</div>
</div>
<!--/container-->


<?php include_once(_INCLUDESDIR_ . "footer.php"); //always load header?>

