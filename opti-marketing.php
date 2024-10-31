<?php
/**
 * OptiMarketing
 *
 * @package       OPTIMARKET
 * @author        Opti Marketing
 * @license       gplv3
 * @version       2.0.9
 *
 * @wordpress-plugin
 * Plugin Name:   Opti Marketing
 * Plugin URI:    https://www.opti.marketing/wordpress-plugin-plataforma-opti-marketing/
 * Description:   Plugin para criar histórico de ranqueamento SEO, análise e avaliação completa dos artigos dos Posts de seu blog na plataforma OPTI MARKETING.
 * Version:       2.0.9
 * Author:        Opti Marketing
 * Author URI:    https://www.opti.marketing/
 * Text Domain:   opti-marketing
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin name
define( 'OPTIMARKET_NAME', 'Opti Marketing' );

// Plugin version
define( 'OPTIMARKET_VERSION', '2.0.9' );

// Plugin Root File
define( 'OPTIMARKET_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'OPTIMARKET_PLUGIN_BASE',	plugin_basename( OPTIMARKET_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'OPTIMARKET_PLUGIN_DIR',	plugin_dir_path( OPTIMARKET_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'OPTIMARKET_PLUGIN_URL',	plugin_dir_url( OPTIMARKET_PLUGIN_FILE ) );

// Plugin api URL
define( 'OPTIMARKET_API_URL', 'https://api.opti.marketing' );


function create_block_opti_marketing_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_opti_marketing_block_init' );

add_action( 'admin_menu', 'optimarketing_init_menu' );

/**
 * Init Admin Menu.
 *
 * @return void
 */
function optimarketing_init_menu() {
  add_menu_page( 
    __( 'Opti Marketing', 'optimarketing'), 
    __( 'Opti Marketing', 'optimarketing'), 
    'manage_options', 'optimarketing', 
    'optimarketing_admin_page', 
    'dashicons-admin-post', 
    '2.1' 
  );
  add_submenu_page( 
    'optimarketing', 
    'Analytics', 
    'Analytics', 
    'manage_options', 
    'analytics', 
    'optimarketing_admin_page', 
    '2.1' 
  );
}

add_action( 'admin_menu', 'register_logpage' );

function register_logpage(){
  add_menu_page('logs', 'logs', 'administrator','logs', 'optimarketing_admin_page');
}

function meu_plugin_add_meta_box() {
  add_meta_box(
    'optimization_tips_meta_box', // ID único do meta box
    'Dicas de Otimização', // Título do meta box
    'optimarketing_admin_page', // Função de callback para renderizar o conteúdo do meta box
    'post', // Tipo de post onde o meta box será exibido (post, page, etc.)
    'normal', // Contexto onde o meta box será exibido (normal, side, etc.)
    'default' // Prioridade do meta box (high, low, etc.)
  );
}
  
// Gancho para adicionar o meta box
add_action('add_meta_boxes', 'meu_plugin_add_meta_box');

// register custom meta tag field
function opti_seo_focuskw_register_post_meta() {
  register_post_meta( 'post', '_opti_seo_focuskw', array(
      'show_in_rest' => true,
      'single' => true,
      'type' => 'string',
      'auth_callback' => function() {
        return current_user_can('edit_posts');
      },
      'sanitize_callback' => 'sanitize_text_field',
      'supports' => array(  'editor', 'title', 'revisions', 'page-attributes', 'custom-fields'  ),
  ) );
}
add_action( 'init', 'opti_seo_focuskw_register_post_meta' );

// register custom meta tag field
function opti_seo_metadesc_register_post_meta() {
  register_post_meta( 'post', '_opti_seo_metadesc', array(
      'show_in_rest' => true,
      'single' => true,
      'type' => 'string',
      'auth_callback' => function() {
        return current_user_can('edit_posts');
      },
      'sanitize_callback' => 'sanitize_text_field',
      'supports' => array(  'editor', 'title', 'revisions', 'page-attributes', 'custom-fields'  ),
  ) );
}
add_action( 'init', 'opti_seo_metadesc_register_post_meta' );

// register custom meta tag field 
function opti_seo_thumbnail_id_register_post_meta() {
  register_post_meta( 'post', '_opti_thumbnail_id', array(
      'show_in_rest' => true,
      'single' => true,
      'type' => 'integer',
      'auth_callback' => function() {
        return current_user_can('edit_posts');
      },
      'sanitize_callback' => 'sanitize_text_field',
      'supports' => array(  'editor', 'title', 'revisions', 'page-attributes', 'custom-fields'  ),
  ) );
}
add_action( 'init', 'opti_seo_thumbnail_id_register_post_meta' );


/**
 * Init Admin Page.
 *
 * @return void
 */
function optimarketing_admin_page() {
  require_once plugin_dir_path( __FILE__ ) . 'templates/app.php';
}

add_action( 'admin_enqueue_scripts', 'optimarketing_admin_enqueue_scripts' );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function optimarketing_admin_enqueue_scripts() {
  $asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

  wp_register_script(
    'optimarketing-script',
    plugins_url( 'build/index.js', __FILE__ ),
    $asset_file['dependencies'],
    $asset_file['version']
  );

  wp_enqueue_style( 'optimarketing-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );
  wp_enqueue_script( 'optimarketing-script', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-element' ), '1.0.0', true );
}

function sendDeactivate ($passwordOpti, $loginOpti, $idUsuarioWordpress ){
  $url = OPTIMARKET_API_URL;
  
  if($loginOpti != null){
    $data_json = wp_json_encode(array(
      'login' => $loginOpti,
      'senha' => $passwordOpti,
    ));
  
    $args = array(
      'body' => $data_json,
      'headers' => array(
        'Content-Type'  => 'application/json',
      ),
      'method' => 'POST'
    );
  
    $response = wp_remote_post($url . '/token', $args);
    $body_json = wp_remote_retrieve_body($response);
  
    $body = json_decode($body_json, true);
      
    $args = array(
      'headers' => array(
        'Authorization'  => 'Bearer ' . $body["token"],
      ),
      'method' => 'POST'
    );
  
    $response = wp_remote_post($url . '/monitoramento/desativar/' . $idUsuarioWordpress, $args);
  }
}

/**
 * temporario:  limpa artigos escolhidos
 */
require_once plugin_dir_path( __FILE__ ) . 'actions/actions.php';

function deactivate_opti() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'opti_configurations';
  $idUsuarioWordpress = $wpdb->get_results("SELECT value FROM $table_name WHERE name = 'idUsuarioWordpress'");
  $loginOpti = $wpdb->get_results("SELECT value FROM $table_name WHERE name = 'loginOpti'");
  $passwordOpti = $wpdb->get_results("SELECT value FROM $table_name WHERE name = 'passwordOpti'");
  if(count($loginOpti) > 0)
  sendDeactivate($passwordOpti[0]->value, $loginOpti[0]->value, $idUsuarioWordpress[0]->value);

  if ($wpdb->get_var("SHOW TABLES LIKE 'wp_opti_logs'") == "wp_opti_logs")
  {
    $wpdb->get_results("drop table wp_opti_logs");
  }

  if ($wpdb->get_var("SHOW TABLES LIKE 'wp_opti_analisys_legibility'") == "wp_opti_analisys_legibility")
  {
    $wpdb->get_results("drop table wp_opti_analisys_legibility");
  }
  
  if ($wpdb->get_var("SHOW TABLES LIKE 'wp_opti_configurations'") == "wp_opti_configurations")
  {
    $wpdb->get_results("drop table wp_opti_configurations");
  }
  
  if ($wpdb->get_var("SHOW TABLES LIKE 'wp_opti_article'") == "wp_opti_article")
  {
    $wpdb->get_results("drop table wp_opti_article");
  }
}

register_deactivation_hook( __FILE__, 'deactivate_opti');

/**
 * Criando tabelas
 * Ao ativat plugin
 */
register_activation_hook(__FILE__, 'activation_opti' );

function activation_opti() {
  $plugins = get_plugins();
  $outrasVersoes = array();

  if(count($plugins) > 0){
    foreach ($plugins as $key => $value) {
      if ($plugins[$key]['Name'] == 'Opti Marketing' && $plugins[$key]['Version'] != OPTIMARKET_VERSION)
      array_push($outrasVersoes, $key);
    }
    if(count($outrasVersoes) > 0)
      delete_plugins($outrasVersoes);
  }

  global $wpdb;
  $result = array();
  $table_name = $wpdb->prefix . 'opti_configurations';
  $table_name_logs = $wpdb->prefix . 'opti_logs';
  $table_name_analisys = $wpdb->prefix . 'opti_analisys_legibility';
  $table_name_article = $wpdb->prefix . 'opti_article';
  $info = array();

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_logs'") != $table_name_logs)
  {
    $wpdb->query("CREATE TABLE wp_opti_logs (
      id int primary key auto_increment,
      message varchar(255),
      exception text,
      date datetime,
      exceptionName varchar(255),
      functionName varchar(255),
      priority int
      );"
    );
  } 

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
  {
    $wpdb->query("CREATE TABLE wp_opti_configurations (
      id INT NOT NULL AUTO_INCREMENT primary key,
      name TEXT NOT NULL, 
      value TEXT NULL);"
    );
  } 

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_analisys'") != $table_name_analisys)
  {    
    $wpdb->query("CREATE TABLE wp_opti_analisys_legibility (
      id int auto_increment primary key,
      idPost int,
      analysis text,
      type varchar(255)) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
    );
  } 

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_article'") != $table_name_article)
  {    
    $wpdb->query("CREATE TABLE wp_opti_article (
      id int auto_increment primary key,
      post_id int,
      article text) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
    );
  }

  add_option( 'Activated_Plugin', 'opti-marketing' );
}

add_action( 'admin_init', 'popup' );
/**
 * Ao ativar plugin
 * Abre popup boas vindas
 */
function popup() {
  if ( get_option( 'Activated_Plugin_Popup' ) == 'opti-marketing' ) {
    wp_enqueue_script('activation_modal', plugins_url('js/activation_modal.js', __FILE__));
    delete_option( 'Activated_Plugin_Popup' );
  }
}


add_action( 'admin_init', 'load_plugin' );
/**
 * Ao ativar plugin
 * Redireciona para a tela do plugin de escolha de posts
 */
function load_plugin() {
  if ( get_option( 'Activated_Plugin' ) == 'opti-marketing' ) {
    delete_option( 'Activated_Plugin' );
    
    $target_url = admin_url('admin.php?page=optimarketing');
    add_option( 'Activated_Plugin_Popup', 'opti-marketing' );
    wp_safe_redirect($target_url);
    exit;
  }
}

function sample_admin_notice__error() {
  $class = 'notice notice-error';
  $message = __( 'O plugin Opti Marketing não é compatível com a versão instalada do wordpress, atualize a versão do wordpress para 6.2.2 ou superior', 'sample-text-domain' );
  //transformar para int e validar
  if (version_compare(get_bloginfo('version'), '6.2.2') == -1) printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
  }
  add_action( 'admin_notices', 'sample_admin_notice__error' );