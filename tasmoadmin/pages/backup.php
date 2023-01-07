<?php

 $devices = $Sonoff->getDevices();


 ?>


<div class='row justify-content-sm-center'>
    <div class='col col-12 col-md-6 '>
        <h2 class='text-sm-center mb-3'>
            <?php echo $title; ?>
        </h2>
    </div>
</div>

<div class='row justify-content-center'>
    <div class='col'>
        <div class='form-row mb-3'>
            <div class='offset-1 col-auto col col-auto'>
                <button type='submit' class='btn btn-success' name='submit' value='submit'>
                    <?php echo __("BTN_START_BACKUP", "DEVICE_UPDATE"); ?>
                </button>
            </div>
            <div class='col col-auto'>
                <div class="form-check pl-0">
                    <input type="checkbox"
                           class="form-check-input showmore d-none"
                           id="showmore"
                           name='showmore'
                    >
                    <label class="form-check-label  btn btn-secondary" for="showmore">
                        <?php echo __("SHOW_MORE", "DEVICES"); ?>
                    </label>
                </div>
            </div>
            <?php if ($Config->read("show_search") == 1): ?>
                <div class="col col-auto">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text"
                                   name="searchterm"
                                   class='form-control device-search has-clearer'
                                   placeholder="<?php echo __("FILTER", "DEVICES"); ?>"
                            >
                            <div class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-search"></i>
										</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class='table-responsive double-scroll'>
            <?php
            $deviceLinks = true;
            $deviceLinkActionText = __("BACKUP", "DEVICE_UPDATE");
            include "elements/devices_table.php";
            ?>
        </div>
    </div>
</div>
<script src="<?php echo $urlHelper->js("devices"); ?>"></script>
