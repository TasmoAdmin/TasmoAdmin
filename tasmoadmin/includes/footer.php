</div>
</main>
<footer class="footer">
	<div class='navbar navbar-expand-lg navbar-dark bg-dark py-0 '>
		<div class="mx-auto">
			<span class="navbar-text py-1">
				<?php if ('0' == $Config->read('hide_copyright')) { ?>
					&copy; <?php echo date('Y'); ?>&nbsp;<?php echo __('BY'); ?> reloxx13 -
				<?php } ?>
				<?php // if( $Config->read( "current_git_tag" ) != "" ) :

                $updateCheck = $Config->read('check_for_updates');
				?>


				<div id="versionHolder" class='d-inline-block' data-update-check='<?php echo $updateCheck; ?>'>
					<?php echo substr($Config->read('current_git_tag'), 0, 15); ?>
					<i id='update-icon'
					   class='fa fa-question <?php echo '0' == $updateCheck ? 'd-none' : ''; ?> '
					   data-current_git_tag='<?php echo substr(
					       $Config->read('current_git_tag'),
					       0,
					       15
					   ); ?>'
					   data-update_channel='<?php echo $Config->read('update_channel'); ?>'
					   style=''
					></i>
				</div><?php // endif;?>
				<span class=''> -</span>
				<a class=''
				   href='https://github.com/TasmoAdmin/TasmoAdmin'
				   target='_blank'
				>
					TasmoAdmin GitHub<?php // echo __( "VIEW_ON_GITHUB" );?>
				</a>
				-
				<a class='' href='https://github.com/arendst/Tasmota'
				   target='_blank'
				>
					Tasmota GitHub<?php // echo __( "VIEW_ON_GITHUB" );?>
				</a>

			</span>
		</div>
	</div>
</footer>
</body>
</html>
