<?php
$file = fopen( $filename, 'r' );
while( ( $line = fgetcsv( $file ) ) !== FALSE ) {
	//$line is an array of the csv elements
	$line[ 1 ] = explode( "|", $line[ 1 ] );
	$devices[] = $line;
}
fclose( $file );


//var_dump( $devices );
?>

<table id='device-list' class='center-table' border='0' cellspacing='0'>
    <thead>
    <tr>
        <th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
        <th><a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=add'>
				<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
            </a>
        </th>
    </tr>
    </thead>
    <tbody>
	<?php
	$odd = TRUE;
	if( isset( $devices ) && !empty( $devices ) ):
		foreach( $devices as $device_group ):
			foreach( $device_group[ 1 ] as $key => $device ): ?>
                <tr class='<?php echo $odd ? "odd" : "even"; ?>'
                    data-device_id='<?php echo $device_group[ 0 ]; ?>'
                    data-device_group='<?php echo count( $device_group[ 1 ] ) > 1 ? "multi" : "single"; ?>'
                    data-device_ip='<?php echo $device_group[ 2 ]; ?>'
                    data-device_relais='<?php echo $key + 1; ?>'
                >
                    <td><?php echo $device_group[ 0 ]; ?></td>
                    <td><a href='http://<?php echo $device_group[ 2 ]; ?>/'
                           target='_blank'
                           title='<?php echo __( "LINK_OPEN_DEVICE_WEBUI", "DEVICES" ); ?>'><?php echo $device; ?></a>
                    </td>
                    <td><?php echo $device_group[ 2 ]; ?></td>
                    <td class='status'>
                        <label class="form-switch">
                            <input type="checkbox">
                            <i></i>
                        </label>

                    </td>
                    <td class='rssi'>
                        <div class='loader'><img
                                    src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
                                    alt='<?php echo __( "TEXT_LOADING" ); ?>'
                                    title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
                    </td>
                    <td class='version'>
                        <div class='loader'><img
                                    src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
                                    alt='<?php echo __( "TEXT_LOADING" ); ?>'
                                    title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
                    </td>
                    <td class='runtime'>
                        <div class='loader'><img
                                    src='<?php echo _RESOURCESDIR_; ?>img/loading.gif'
                                    alt='<?php echo __( "TEXT_LOADING" ); ?>'
                                    title='<?php echo __( "TEXT_LOADING" ); ?>'></div>
                    </td>
                    <td>
                        <a href='<?php echo _APPROOT_; ?>index.php?page=device_config&action=delete&device_id=<?php echo $device_group[ 0 ]; ?>'>
							<?php echo __( "LINK_DEVICE_CONFIG", "DEVICES" ); ?>
                        </a>
                        <a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=edit&device_id=<?php echo $device_group[ 0 ]; ?>'>
							<?php echo __( "LINK_DEVICE_EDIT", "DEVICES" ); ?>
                        </a>
                        <a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=delete&device_id=<?php echo $device_group[ 0 ]; ?>'>
							<?php echo __( "LINK_DEVICE_DELETE", "DEVICES" ); ?>
                        </a>
                    </td>
                </tr>
				<?php
				$odd = !$odd;
			endforeach;
		endforeach;
	endif; ?>
    </tbody>
    <tfoot>
    <tr class='bottom'>
        <th><?php echo __( "TABLE_HEAD_ID", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_NAME", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_IP", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_STATE", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_RSSI", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_VERSION", "DEVICES" ); ?></th>
        <th><?php echo __( "TABLE_HEAD_RUNTIME", "DEVICES" ); ?></th>
        <th><a href='<?php echo _APPROOT_; ?>index.php?page=device_action&action=add'>
				<?php echo __( "TABLE_HEAD_NEW_DEVICE", "DEVICES" ); ?>
            </a>
        </th>
    </tr>
    </tfoot>
</table>


<script type='text/javascript' src='<?php echo _RESOURCESDIR_; ?>js/devices.js'></script>