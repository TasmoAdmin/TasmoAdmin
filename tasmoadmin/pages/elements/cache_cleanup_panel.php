<div class="row mt-5">
    <div class="col col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="h4 mb-3"><?php echo __('CONFIG_MAINTENANCE_TITLE', 'USER_CONFIG'); ?></h2>
                <p class="mb-2"><?php echo __('CONFIG_CACHE_CLEAR_SCOPE', 'USER_CONFIG'); ?></p>
                <p class="text-body-secondary mb-4"><?php echo __('CONFIG_CACHE_CLEAR_SAFE', 'USER_CONFIG'); ?></p>
                <form method="post" class="mb-0">
                    <button type="submit" class="btn btn-outline-secondary" name="clean_temp_cache" value="1">
                        <?php echo __('BTN_CLEAR_CACHE', 'USER_CONFIG'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
