<footer class="footer">
	<div class='navbar navbar-expand-lg navbar-dark bg-dark '>
		<div class="container  d-flex justify-content-center">
			<span class="navbar-text ">
		        &copy; <?php echo date( "Y" ); ?> <?php echo __( "BY" ); ?> reloxx13

				<?php if ( $Config->read( "current_git_tag" ) != "" ): ?>

					- rev <?php echo substr( $Config->read( "current_git_tag" ), 0, 7 ); ?>

				<?php endif; ?>

				<span class=''> - </span><a class=''
				                            href='https://github.com/reloxx13/SonWEB'
				                            target='_blank'>
					SonWEB GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
				</a>
				- <a class='' href='https://github.com/arendst/Sonoff-Tasmota'
				     target='_blank'>
					Tasmota GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
				</a>
			</span>
		</div>
	</div>
</footer>

<script>
	$( "#content-holder" ).css(
		"width", "calc(100% - " + (
		         $( "#navi" ).innerWidth() + 42
	) + "px)"
	);
	var nightmodeconfig = "<?php echo $Config->read( "nightmode" ); ?>";
</script>
</body>
</html>
