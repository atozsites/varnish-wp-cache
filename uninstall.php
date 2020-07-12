<?php

// Uninstallation script: Removed options from the database.
// This code is executed if the plugin is uninstalled (deleted) through the
// WordPress Plugin Management interface.

if ( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
 }

delete_option('atozsitesVarnish_addr');
delete_option('atozsitesVarnish_port');
delete_option('atozsitesVarnish_secret');
delete_option('atozsitesVarnish_timeout');
delete_option('atozsitesVarnish_purge_url');
delete_option('atozsitesVarnish_update_pagenavi');
delete_option('atozsitesVarnish_update_commentnavi');
delete_option('atozsitesVarnish_use_adminport');
delete_option('atozsitesVarnish_vversion');

?>