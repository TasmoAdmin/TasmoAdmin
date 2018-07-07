<div class="modal fade"
     id="deleteDeviceModal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="deleteDeviceModalLabel"
     aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-dark" id="deleteDeviceModalLabel">
					<?php echo __( "DELETE_DEVICE_CONFIRM_TITLE", "DEVICES" ); ?>
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __( "CLOSE" ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-dark">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<?php echo __( "CANCEL" ); ?>
				</button>
				<a type="button" class="btn btn-ok btn-primary text-white">
					<?php echo __( "DELETE_DEVICE", "DEVICES" ); ?>
				</a>
			</div>
		</div>
	</div>
</div>