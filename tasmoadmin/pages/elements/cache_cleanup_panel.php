<div class="cache-cleanup-panel settings-section">
    <div class="cache-cleanup-copy">
        <h2 class="settings-section-title"><?php echo __('CONFIG_MAINTENANCE_TITLE', 'USER_CONFIG'); ?></h2>
    </div>
    <div class="cache-cleanup-row">
        <div class="cache-cleanup-row-copy">
            <p class="cache-cleanup-lead"><?php echo __('CONFIG_CACHE_CLEAR_SCOPE', 'USER_CONFIG'); ?></p>
            <p class="text-body-secondary mb-0"><?php echo __('CONFIG_CACHE_CLEAR_SAFE', 'USER_CONFIG'); ?></p>
        </div>
        <div class="cache-cleanup-actions">
            <form method="post" class="cache-cleanup-form">
                <button type="submit" class="btn btn-outline-secondary" name="clean_temp_cache" value="1">
                    <?php echo __('BTN_CLEAR_CACHE', 'USER_CONFIG'); ?>
                </button>
            </form>
        </div>
    </div>
    <div class="cache-cleanup-row">
        <div class="cache-cleanup-row-copy">
            <p class="cache-cleanup-lead"><?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?></p>
            <p class="text-body-secondary mb-0"><?php echo __('CONFIG_AUTOSCAN_HELP', 'USER_CONFIG'); ?></p>
        </div>
        <div class="cache-cleanup-actions">
            <a href="<?php echo _BASEURL_; ?>devices_autoscan" class="btn btn-outline-primary">
                <?php echo __('DEVICES_AUTOSCAN', 'NAVI'); ?>
            </a>
        </div>
    </div>
</div>
