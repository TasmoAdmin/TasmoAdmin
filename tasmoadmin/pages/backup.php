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
