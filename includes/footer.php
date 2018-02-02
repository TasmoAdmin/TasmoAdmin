<div class='clear'></div>
<div id='footer'>
    &copy; <?php echo date( "Y" ); ?> <?php echo __( "BY" ); ?> reloxx13
	<?php if ( $Config->read( "current_git_sha" ) != "" ): ?>
        - rev <?php echo substr( $Config->read( "current_git_sha" ), 0, 7 ); ?>
	<?php endif; ?>
    - <a href='https://github.com/reloxx13/SonWEB' title='SonWEB <?php echo __( "VIEW_ON_GITHUB" ); ?>' target='_blank'>
        SonWEB GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
    </a>
    - <a href='https://github.com/arendst/Sonoff-Tasmota'
         title='Tasmota <?php echo __( "VIEW_ON_GITHUB" ); ?>'
         target='_blank'>
        Tasmota GitHub<?php //echo __( "VIEW_ON_GITHUB" ); ?>
    </a>
</div>
<script>
    $( "#content-holder" ).css(
        "width", "calc(100% - " + (
                 $( "#navi" ).innerWidth() + 42
    ) + "px)"
    );
</script>
</body>
</html>
