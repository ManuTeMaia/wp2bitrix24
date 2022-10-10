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
      'Настройки импорта лидов в Bitrix24',
      'Bitrix24',
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
  	add_settings_section( 'wp2btx_settings_page_main', 'Основные настройки', '', 'wp2btx_settings_page' );


    register_setting('wp2btx_settings_page', 'wp2btx_webhook');
    add_settings_field(
      $id = 'wp2btx_webhook',
      $title = 'Вебхук для вызова rest api Bitrix24',
      $callback = [$this, 'wp2btx_webhook_display'],
      $page = 'wp2btx_settings_page',
      $section = 'wp2btx_settings_page_main'
    );

    register_setting('wp2btx_settings_page', 'wp2btx_prefix');
    add_settings_field(
      $id = 'wp2btx_prefix',
      $title = 'Префикс лида',
      $callback = [$this, 'wp2btx_prefix_display'],
      $page = 'wp2btx_settings_page',
      $section = 'wp2btx_settings_page_main'
    );

  }


  function wp2btx_webhook_display(){
    $name = 'wp2btx_webhook';
    printf('<input type="text" name="%s" value="%s" />', $name, get_option($name));
    ?>
    <p>Ссылка вида https://irsu.bitrix24.ru/rest/1/15ngm7grby3uo7lr/ из личного кабинета Bitrix24</p>
    <?php
  }

  function wp2btx_prefix_display(){
    $name = 'wp2btx_prefix';
    printf('<input type="text" name="%s" value="%s" />', $name, get_option($name, 'Store-'));
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
    <h3>Check Server:</h3> 
    <p><?php CRest::checkServer(); ?></p>
    <?php print_r(get_option('wp2btx_webhook')); 
    $result = CRest::call("crm.lead.fields");

echo '<pre>';
	print_r($result);
echo '</pre>';
?>
    <hr />
    <p><a href="mailto:sup@manutemaia.com">Техническая поддержка</a></p>
  	</div>
  	<?php
  }
}
new WP2BTX_Admin_UI;
