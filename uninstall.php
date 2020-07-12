<?php

// Uninstallation script: Removed options from the database.
// This code is executed if the plugin is uninstalled (deleted) through the
// WordPress Plugin Management interface.

if ( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
 }

delete_option('BestwebsiteVarnish_addr');
delete_option('BestwebsiteVarnish_port');
delete_option('BestwebsiteVarnish_secret');
delete_option('BestwebsiteVarnish_timeout');
delete_option('BestwebsiteVarnish_purge_url');
delete_option('BestwebsiteVarnish_update_pagenavi');
delete_option('BestwebsiteVarnish_update_commentnavi');
delete_option('BestwebsiteVarnish_use_adminport');
delete_option('BestwebsiteVarnish_vversion');

?>