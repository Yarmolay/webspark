<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.1.0
 * @package    Webspark
 * @subpackage Webspark/admin/partials
 */
?>
<form method="post" action="<?php echo admin_url('options.php'); ?>">
    <?php wp_nonce_field(); ?>
    <input type="hidden" name="action" value="webspark_settings">
    <?php settings_fields('webspark-settings');?>
    <?php do_settings_sections('webspark-settings')?>
    <?php submit_button();?>
</form>

<div>

</div>
