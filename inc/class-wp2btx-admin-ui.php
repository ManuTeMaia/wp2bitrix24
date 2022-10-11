<?php

/**
 * WordPress Admin UI
 */
class WP2BTX_Admin_UI {

  function __construct() {

    add_action('admin_menu', [$this, 'add_plugin_page']);

    add_action('admin_init', [$this, 'plugin_settings']);

  }


  /**
   * Создаем страницу настроек плагина
   */
  function add_plugin_page(){
  	add_options_page(
      __('Export leads to Bitrix24 CRM settings', 'irs_btx'),
      __('Export to Bitrix24', 'irs_btx'),
      'manage_options',
      'wp2btx_settings_page',
      [$this, 'bitrix24s_options_page_output']
    );
  }

  /**
   * Регистрируем настройки.
   * Настройки будут храниться в массиве, а не одна настройка = одна опция.
   */
  function plugin_settings(){

  	// параметры: $id, $title, $callback, $page
  	add_settings_section( 'wp2btx_settings_page_main', __('General Settings', 'irs_btx'), '', 'wp2btx_settings_page' );


    register_setting('wp2btx_settings_page', 'wp2btx_webhook');
    add_settings_field(
      $id = 'wp2btx_webhook',
      $title = __('CRest webhook', 'irs_btx'),
      $callback = [$this, 'wp2btx_webhook_display'],
      $page = 'wp2btx_settings_page',
      $section = 'wp2btx_settings_page_main'
    );

    register_setting('wp2btx_settings_page', 'wp2btx_prefix');
    add_settings_field(
      $id = 'wp2btx_prefix',
      $title = __('Lead Prefix', 'irs_btx'),
      $callback = [$this, 'wp2btx_prefix_display'],
      $page = 'wp2btx_settings_page',
      $section = 'wp2btx_settings_page_main'
    );

    register_setting('wp2btx_settings_page', 'wp2btx_user_id');
    add_settings_field(
      $id = 'wp2btx_user_id',
      $title = __('Bitrix24 User ID', 'irs_btx'),
      $callback = [$this, 'wp2btx_user_id_display'],
      $page = 'wp2btx_settings_page',
      $section = 'wp2btx_settings_page_main'
    );

  }


  function wp2btx_webhook_display(){
    $name = 'wp2btx_webhook';
    printf('<input type="text" name="%s" value="%s" style="width: 500px;" />', $name, get_option($name));
    printf('<p>%s https://inc.bitrix24.ru/rest/1/15ngm7grby3uo7lr/. %s</p>', __('Link like', 'irs_btx'), __('You can take it from Bitrix24', 'irs_btx'));
  }

  function wp2btx_prefix_display(){
    $name = 'wp2btx_prefix';
    printf('<input type="text" name="%s" value="%s" />', $name, get_option($name, 'Store-'));
  }

  function wp2btx_user_id_display(){
    $name = 'wp2btx_user_id';
    printf('<input type="text" name="%s" value="%s" />', $name, get_option($name, '1'));
    printf('<p>%s https://inc.bitrix24.ru/company/personal/user/8/. %s</p>', __('You can get the Bitrix24 User ID from link like this', 'irs_btx'), __('In this example "8" is the User ID', 'irs_btx'));
  }

  function bitrix24s_options_page_output(){
  	?>
  	<div class="wrap">
  		<h2><?php echo get_admin_page_title() ?></h2>
  		<form action="options.php" method="POST">
  			<?php
  				settings_fields( 'wp2btx_settings_page' );     
  				do_settings_sections( 'wp2btx_settings_page' ); 
  				submit_button();
  			?>
  		</form>
    <h3><?php _e('Check Server', 'irs_btx'); ?></h3> 
    <p><?php CRest::checkServer(); ?></p>
    <?php print_r(get_option('wp2btx_webhook')); 
    $result = CRest::call("profile");
        echo '<pre>';
            print_r($result);
        echo '</pre>';
?>
    <hr />
    <p><a href="mailto:sup@manutemaia.com"><?php _e('Support', 'irs_btx'); ?></a></p>
  	</div>
<?php
  }
}
new WP2BTX_Admin_UI;
