<?php 
/**
  Plugin Name: Manu WP2Bitrix24
  Plugin URI: https://manutemaia.com
  Description: WP2Bitrix24 - WordPress plugin to upload leads from WooCommerce orders to Bitrix24 CRM.
  Author: Ekaterina Manzhosova
  Version: 0.0.1
  Author URI: https://manutemaia.com
  Text Domain: irs_btx

 *  PHP version 7.4.0

  @category ManuScripts
  @package  ManuScripts
  @author   Ekaterina Manzhosova <cath@manutemaia.com>
  @license  https://manutemaia.com commercial
  @link     https://manutemaia.com
 * */
defined('ABSPATH') or die("No script kiddies please!");
define('WP2BTX_PLUGIN_URL', plugin_dir_url( __FILE__ ));

/** CREST Settings */
define('C_REST_WEB_HOOK_URL',get_option('wp2btx_webhook'));//url on crest Webhook

//define('C_REST_CURRENT_ENCODING','windows-1251');
//define('C_REST_IGNORE_SSL',true);//turn off validate ssl by curl
define('C_REST_LOG_TYPE_DUMP',true); //logs save var_export for viewing convenience
//define('C_REST_BLOCK_LOG',true);//turn off default logs
define('C_REST_LOGS_DIR', WP2BTX_PLUGIN_URL .'/logs/'); //directory path to save the log

require_once 'vendor/crest.php';
require_once 'inc/class-wp2btx-admin-ui.php';
require_once 'inc/class-wp2btx-worker.php';

register_activation_hook( __FILE__, 'wp2btx_activate' );
register_deactivation_hook( __FILE__, 'wp2btx_deactivate' );

function wp2btx_activate() {

	flush_rewrite_rules();
}

function wp2btx_deactivate() {
	flush_rewrite_rules();
}

function crest_install() {
  $result = CRest::installApp();
if($result['rest_only'] === false):?>
	<head>
		<script src="//api.bitrix24.com/api/v1/"></script>
		<?php if($result['install'] == true):?>
			<script>
				BX24.init(function(){
					BX24.installFinish();
				});
			</script>
		<?php endif;?>
	</head>
	<body>
		<?php if($result['install'] == true):?>
			installation has been finished
		<?php else:?>
			installation error
		<?php endif;?>
	</body>
<?php endif;
}


