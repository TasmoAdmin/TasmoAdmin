<footer class="footer">
	<div class='navbar navbar-expand-lg navbar-dark bg-dark py-0 '>
		<div class="mx-auto">
			<span class="navbar-text py-1">
				&copy; <?php echo date( "Y" ); ?> <?php echo __( "BY" ); ?> reloxx13

				<?php //if( $Config->read( "current_git_tag" ) != "" ) :

					$updateCheck = $Config->read( "check_for_updates" );
				?>

				-
				<div id="versionHolder" class='d-inline-block' data-update-check='<?php echo $updateCheck; ?>'>
					<?php echo substr( $Config->read( "current_git_tag" ), 0, 15 ); ?>
					<i id='update-icon'
					   class='fa fa-question <?php echo( $updateCheck == "0" ? "d-none" : "" ); ?> '
					   data-current_git_tag='<?php echo substr(
						   $Config->read( "current_git_tag" ),
						   0,
						   15
					   ); ?>'
					   style=''></i>
				</div><?php //endif; ?>
				<span class=''> -</span>
				<a class=''
				   href='https://github.com/reloxx13/TasmoAdmin'
				   target='_blank'>
					TasmoAdmin GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
				</a>
				-
				<a class='' href='https://github.com/arendst/Sonoff-Tasmota'
				   target='_blank'>
					Tasmota GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
				</a>

			</span>
		</div>
	</div>
</footer>

<script>
	//$( "#content-holder" ).css(
	//	"width", "calc(100% - " + (
	//	         $( "#navi" ).innerWidth() + 42
	//) + "px)"
	//);
	var nightmodeconfig = "<?php echo $Config->read( "nightmode" ); ?>";
</script>
</body>
</html>
