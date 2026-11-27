<?php

use TasmoAdmin\Health\HealthState;
use TasmoAdmin\Health\HealthView;

$healthState = $healthRow['state'] ?? HealthState::UNKNOWN;
$healthLabel = __(HealthView::labelKey($healthState), 'HEALTH');
?>
<span class="health-state-dot <?php echo HealthView::dotClass($healthState); ?>"
      data-bs-toggle="tooltip"
      data-bs-title="<?php echo htmlspecialchars($healthLabel, ENT_QUOTES, 'UTF-8'); ?>"
      aria-label="<?php echo htmlspecialchars($healthLabel, ENT_QUOTES, 'UTF-8'); ?>"></span>
