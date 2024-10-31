<?php
/**
 * Action para verificar a existência da tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_exist_table_opti', 'exist_table_opti');
add_action('wp_ajax_exist_table_opti', 'exist_table_opti');

function exist_table_opti() {
  global $wpdb;
  $result = array();
  $table_name = $wpdb->prefix . 'opti_configurations';

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
    
    $result['info'] = "A tabela '$table_name' existe.";
    
    return wp_send_json_success($result); 
  } else {
    $result['info'] = "A tabela '$table_name' não existe.";
    return wp_send_json_error($result);
  }
}

/**
 * Action para salvar login(email) e senha do usuário OPTI na tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_save_user_opti', 'save_user_opti');
add_action('wp_ajax_save_user_opti', 'save_user_opti');

function save_user_opti() {
  global $wpdb;

  $idUsuarioWordpress = $_POST['idUsuarioWordpress'];
  $idUsuarioOpti = $_POST['idUsuarioOpti'];
  $login = $_POST['login'];
  $password = $_POST['password'];

  $existing_idUserWp = $wpdb->get_var($wpdb->prepare("SELECT value FROM wp_opti_configurations WHERE name = %s", 'idUsuarioWordpress'));
  $existing_idUsuarioOpti = $wpdb->get_var($wpdb->prepare("SELECT value FROM wp_opti_configurations WHERE name = %s", 'idUsuarioOpti'));
  $existing_login = $wpdb->get_var($wpdb->prepare("SELECT value FROM wp_opti_configurations WHERE name = %s", 'loginOpti'));
  $existing_password = $wpdb->get_var($wpdb->prepare("SELECT value FROM wp_opti_configurations WHERE name = %s", 'passwordOpti'));

  if ($existing_idUsuarioOpti === null && $existing_idUserWp === null && $existing_login === null && $existing_password === null) {
    try {
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('idUsuarioWordpress','$idUsuarioWordpress');");
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('idUsuarioOpti','$idUsuarioOpti');");
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('loginOpti','$login');");
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('passwordOpti','$password');");
    }
    catch(Exception $e){
      echo $e->getMessage();
      save_log_php($e);
    }
  }
}

/**
 * Action para buscar metadados yoast tabela wp_postmeta.
 *
 */
add_action('wp_ajax_nopriv_get_yoast_seo_focuskw', 'get_yoast_seo_focuskw');
add_action('wp_ajax_get_yoast_seo_focuskw', 'get_yoast_seo_focuskw');

function get_yoast_seo_focuskw() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'postmeta';

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
    
    $yoast_wpseo_meta_values = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $table_name WHERE meta_key = '_yoast_wpseo_focuskw'"));

    if ($yoast_wpseo_meta_values) {
      $result = array();
      foreach ($yoast_wpseo_meta_values as $meta_value) {
        $result[] = array(
          'post_id' => $meta_value->post_id,
          'meta_value' => $meta_value->meta_value
        );
      }
      return wp_send_json_success($result);
    } else {
      $result['info'] = "Nenhum registro encontrado para '_yoast_wpseo_focuskw'.";
      return wp_send_json_error($result);
    }
  } else {
    $result['info'] = "A tabela '$table_name' não existe.";
    return wp_send_json_error($result);
  }
}

/**
 * Action para buscar dados de login e senha da tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_get_user_opti', 'get_user_opti');
add_action('wp_ajax_get_user_opti', 'get_user_opti');

function get_user_opti() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'opti_configurations';

  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
    
    $idUsuarioWordpress = "SELECT value FROM $table_name WHERE name = 'idUsuarioWordpress'";
    $idUsuarioOpti = "SELECT value FROM $table_name WHERE name = 'idUsuarioOpti'";
    $loginOpti = "SELECT value FROM $table_name WHERE name = 'loginOpti'";
    $passwordOpti = "SELECT value FROM $table_name WHERE name = 'passwordOpti'";

    $user['idUsuarioWordpress'] = $wpdb->get_results($idUsuarioWordpress)[0]->value;
    $user['idUsuarioOpti'] = $wpdb->get_results($idUsuarioOpti)[0]->value;
    $user['loginOpti'] = $wpdb->get_results($loginOpti)[0]->value;
    $user['passwordOpti'] = $wpdb->get_results($passwordOpti)[0]->value;
    
    return wp_send_json_success($user); 
  } else {
    $result['info'] = "A tabela '$table_name' não existe.";
    return wp_send_json_error($result);
  }
}

/**
 * Action para salvar posts selecionados para analise na versão free na tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_save_article', 'save_article');
add_action('wp_ajax_save_article', 'save_article');

function save_article() {
  global $wpdb;

  $post_id = $_POST['postId'];
  $article = $_POST['article'];

  try {
    $exists = "SELECT post_id, article FROM wp_opti_article WHERE post_id = $post_id";

    $result = $wpdb->get_results($exists);
    if(count($result) > 0) {
      $wpdb->query("UPDATE wp_opti_article SET article = '$article' WHERE post_id = $post_id;");
    }else{
      $wpdb->query("INSERT INTO wp_opti_article (post_id, article) VALUES ($post_id, '$article');");
    }
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_delete_article', 'delete_article');
add_action('wp_ajax_delete_article', 'delete_article');

function delete_article() {
  global $wpdb;

  $post_id = $_POST['postId'];

  try {
    $sql = "delete FROM wp_opti_article WHERE post_id = $post_id";

    $wpdb->query($sql);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para buscar dados de posts escolhidos da tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_get_articles_opti', 'get_articles_opti');
add_action('wp_ajax_get_articles_opti', 'get_articles_opti');

function get_articles_opti() {
  try {
    global $wpdb;

    $exists = "SELECT post_id, article FROM wp_opti_article";

    $result = $wpdb->get_results($exists);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para buscar logs.
 *
 */
add_action('wp_ajax_nopriv_get_logs', 'get_logs');
add_action('wp_ajax_get_logs', 'get_logs');

function get_logs() {
  try {
    global $wpdb;

    $exists = "SELECT * FROM wp_opti_logs";

    $result = $wpdb->get_results($exists);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para buscar dados de posts escolhidos da tabela wp_opti_configurations.
 *
 */
add_action('wp_ajax_nopriv_get_focuskw_opti', 'get_focuskw_opti');
add_action('wp_ajax_get_focuskw_opti', 'get_focuskw_opti');

function get_focuskw_opti() {
  try {
    global $wpdb;
    $post_id = $_POST['idPost'];
    $exists = "SELECT meta_value FROM wp_postmeta Where post_id = '$post_id' AND meta_key like '%_opti_seo_focuskw%'";

    $result = $wpdb->get_results($exists);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_get_all_focuskw_opti', 'get_all_focuskw_opti');
add_action('wp_ajax_get_all_focuskw_opti', 'get_all_focuskw_opti');

function get_all_focuskw_opti() {
  try {
    global $wpdb;
    $exists = "SELECT post_id,meta_value FROM wp_postmeta Where meta_key like '%_opti_seo_focuskw%'";

    $result = $wpdb->get_results($exists);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para buscar metas para criação de estrutura pra analises.
 *
 */
add_action('wp_ajax_nopriv_get_meta_post', 'get_meta_post');
add_action('wp_ajax_get_meta_post', 'get_meta_post');

function get_meta_post() {
  $id = $_POST['id'];

  $result = array();
  $post = get_post($id);
  
  if (has_post_thumbnail( $id ) ){
    $result['image'] = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' )[0];
    $result['imageAlt'] = get_post_meta ( get_post_thumbnail_id( $id ), '_wp_attachment_image_alt', true );
  }
  $link = str_contains(get_permalink($post), "http://localhost:8888") ? 'https://blog.opti.marketing/seo-off-page-guia-completo/' : get_permalink($post);
  $result['html'] = file_get_contents( $link );
  return wp_send_json_success($result);
}

/**
 * Action para buscar url base do blog.
 *
 */
add_action('wp_ajax_nopriv_get_configuration_analytics', 'get_configuration_analytics');
add_action('wp_ajax_get_configuration_analytics', 'get_configuration_analytics');

function get_configuration_analytics() {
  $result = array();
  global $wpdb;

  $result['url_base'] = get_site_url();
  $result['accountAnalytics'] = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'accountAnalytics'")[0]->value;
  $result['propertyAnalytics'] = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'propertyAnalytics'")[0]->value;
  return wp_send_json_success($result);
}

/**
 * Action para salvar dados do analytics.
 *
 */
add_action('wp_ajax_nopriv_save_configuration_analytics', 'save_configuration_analytics');
add_action('wp_ajax_save_configuration_analytics', 'save_configuration_analytics');

function save_configuration_analytics() {
  global $wpdb;

  $account = $_POST['account'];
  $property = $_POST['property'];
  try {
    $exists = "SELECT value FROM wp_opti_configurations WHERE name = 'accountAnalytics'";

    $result = $wpdb->get_results($exists);
    if(count($result) > 0) {
      $wpdb->query("update wp_opti_configurations set value = '$account' where name = 'accountAnalytics';");
      $wpdb->query("update wp_opti_configurations set value = '$property' where name = 'propertyAnalytics';");
    }else{
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('accountAnalytics', '$account');");
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('propertyAnalytics', '$property');");
    }
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Salvar token opti no banco.
 *
 */
add_action('wp_ajax_nopriv_save_token', 'save_token');
add_action('wp_ajax_save_token', 'save_token');

//TODO: fazer metodo generico para validar insert e update na tabela de config
function save_token() {
  global $wpdb;
  $token = $_POST['token'];
  $refreshToken = $_POST['refreshToken'];
  $idUsuario = $_POST['idUsuario'];
  $idUsuarioWordpress = $_POST['idUsuarioWordpress'];

  try {
//TODO: refatorar
    $result = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'tokenOpti'");
    if(count($result) > 0 && $token != "undefined" && $token != null && $token != "") 
    {
      $wpdb->query("update wp_opti_configurations set value = '$token' where name = 'tokenOpti';");
      $wpdb->query("update wp_opti_configurations set value = '$refreshToken' where name = 'refreshTokenOpti';");
    }
    else if ($token != "undefined" && $token != null && $token != "")
    {
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('tokenOpti', '$token');");
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('refreshTokenOpti', '$refreshToken');");
    }

    $result = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'idUsuarioWordpress'");
    
    if(count($result) > 0 && $idUsuarioWordpress != "undefined" && $idUsuarioWordpress != null && $idUsuarioWordpress != "") 
    {
      $wpdb->query("update wp_opti_configurations set value = '$idUsuarioWordpress' where name = 'idUsuarioWordpress';");
    }
    else if ($idUsuarioWordpress != "undefined" && $idUsuarioWordpress != null && $idUsuarioWordpress != "")
    {
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('idUsuarioWordpress', '$idUsuarioWordpress');");
    }

    $result_user_opti = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'idUsuarioOpti'");
    
    if(count($result_user_opti) > 0 && $idUsuario != "undefined" && $idUsuario != null && $idUsuario != "") 
    {
      $wpdb->query("update wp_opti_configurations set value = '$idUsuario' where name = 'idUsuarioOpti';");
    }
    else if ($idUsuario != "undefined" && $idUsuario != null && $idUsuario != "")
    {
      $wpdb->query("INSERT INTO wp_opti_configurations (name, value) VALUES ('idUsuarioOpti', '$idUsuario');");
    }
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para salvar analise de legibilidade.
 *
 */
add_action('wp_ajax_nopriv_save_analisys_legibility', 'save_analisys_legibility');
add_action('wp_ajax_save_analisys_legibility', 'save_analisys_legibility');
function save_analisys_legibility(){
  try {
    global $wpdb;

    $idPost = $_POST['idPost'];
    $analysis = $_POST['analysis'];
    $type = $_POST['type'];

    $exists = "SELECT * FROM wp_opti_analisys_legibility WHERE idPost = $idPost and type = '$type'";

    $result = $wpdb->get_results($exists);
    if(count($result) > 0) {
      $wpdb->query("update wp_opti_analisys_legibility set analysis = '$analysis' WHERE idPost = $idPost and type = '$type';");
    }else{
      $wpdb->query("INSERT INTO wp_opti_analisys_legibility (idPost, analysis, type) VALUES ($idPost, '$analysis', '$type');");
    }
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para obter analise de legibilidade.
 *
 */
add_action('wp_ajax_nopriv_get_analisys_legibility', 'get_analisys_legibility');
add_action('wp_ajax_get_analisys_legibility', 'get_analisys_legibility');
function get_analisys_legibility(){
  try {
    global $wpdb;

    $idPost = $_POST['idPost'];
    $type = $_POST['type'];

    $exists = "SELECT * FROM wp_opti_analisys_legibility WHERE idPost = $idPost and type = '$type'";

    $result = $wpdb->get_results($exists);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para obter token da opti.
 *
 */

add_action('wp_ajax_nopriv_get_token_opti', 'get_token_opti');
add_action('wp_ajax_get_token_opti', 'get_token_opti');
function get_token_opti() {
  try {
    global $wpdb;
    $token = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'tokenOpti';");
    $refreshToken = $wpdb->get_results("SELECT value FROM wp_opti_configurations WHERE name = 'refreshTokenOpti';");

    $result = array();

    $result['token_opti'] = $token[0]->value;
    $result['refreshToken_opti'] = $refreshToken[0]->value;
    
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
    return $e->getMessage();
  }
}

add_action('wp_ajax_nopriv_get_id_blog', 'get_id_blog');
add_action('wp_ajax_get_id_blog', 'get_id_blog');
function get_id_blog() {
  try {
    global $wpdb;
    $id_blog = $wpdb->get_results("SELECT `value` FROM wp_opti_configurations WHERE name like '%idUsuarioWordpress%';");

    $result = array();

    $result['id_blog_opti'] = $id_blog[0]->value;
    
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
    return $e->getMessage();
  }
}

add_action('wp_ajax_nopriv_get_data_posts', 'get_data_posts');
add_action('wp_ajax_get_data_posts', 'get_data_posts');
function get_data_posts(){
  try {
    
    global $wpdb;

    $posts = "
      SELECT
        DISTINCT wp_posts.post_title AS 'title',
        wp_posts.id,
        wp_posts.guid AS 'link',
        wp_posts.post_name AS 'slug',
        wp_posts.post_date AS 'post_date',
        GROUP_CONCAT(wp_terms.name) AS 'tags',
        wp_categories.name AS 'category'
      FROM wp_posts
      LEFT JOIN wp_term_relationships ON wp_posts.ID = wp_term_relationships.object_id
      LEFT JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
      LEFT JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id
      LEFT JOIN wp_term_relationships AS wp_category_relationships ON wp_posts.ID = wp_category_relationships.object_id
      LEFT JOIN wp_term_taxonomy AS wp_category_taxonomy ON wp_category_relationships.term_taxonomy_id = wp_category_taxonomy.term_taxonomy_id
      LEFT JOIN wp_terms AS wp_categories ON wp_category_taxonomy.term_id = wp_categories.term_id
      WHERE wp_posts.post_type = 'post' AND wp_posts.post_status = 'publish'
      GROUP BY wp_posts.ID;
    ";

    $result = $wpdb->get_results($posts);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_get_content_post', 'get_content_post');
add_action('wp_ajax_get_content_post', 'get_content_post');
function get_content_post(){
  try {
    global $wpdb;
    $post_id = $_POST['post_id'];

    $content_post = "
      SELECT post_content AS 'html' FROM wp_posts 
      WHERE post_type = 'post' AND ID = '$post_id'
    ";

    $result = $wpdb->get_results($content_post);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_get_posts_selected', 'get_posts_selected');
add_action('wp_ajax_get_posts_selected', 'get_posts_selected');
function get_posts_selected(){
  try {
    global $wpdb;
    $ids = json_decode($_POST['ids']);
    $publicado = $_POST['publicado'];

    $sql = "select * from wp_posts where";
    if ($publicado == "true") $sql .= " post_status = 'publish' and";
    foreach ($ids as $key => $id) {
      if (sizeof($ids) == 1) $sql .= " id = $id;";
      else if ($key == 0) $sql .= " ( id = $id";
      else if ($key == sizeof($ids) - 1) $sql .= " or id = $id );";
      else $sql .= " or id = $id";
    }
    

    $result = $wpdb->get_results($sql);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_get_infor_post', 'get_infor_post');
add_action('wp_ajax_get_infor_post', 'get_infor_post');
function get_infor_post(){
  try {
    global $wpdb;

    $post_id = $_POST['post_id'];

    $posts = "
      SELECT
      DISTINCT p.post_title AS 'title',
      p.post_content AS 'html',
      p.guid AS 'link',
      p.post_name AS 'slug',
      u.display_name AS 'name_author',
      IFNULL(pm.meta_value, '') AS 'keyword',
      p.post_date AS 'post_date',
      (p.post_status = 'publish') AS 'isPublish'
      FROM wp_posts p 
      LEFT JOIN wp_users u ON p.post_author = u.`ID`
      LEFT JOIN (
        SELECT post_id, meta_value
        FROM wp_postmeta
        WHERE
          (meta_key LIKE '%_opti_seo_focuskw%' OR meta_key LIKE '%_yoast_wpseo_focuskw%')
          AND meta_value IS NOT NULL
          LIMIT 1
          ) 
        pm ON p.ID = pm.post_id 
      WHERE p.post_type = 'post' AND p.`ID` = '$post_id'
    ";

    $result = $wpdb->get_results($posts);
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

/**
 * Action para salvar logs.
 *
 */
add_action('wp_ajax_nopriv_save_log', 'save_log');
add_action('wp_ajax_save_log', 'save_log');

function save_log() {
  global $wpdb;

  $message = $_POST['message'];
  $exception = $_POST['exception'];
  $exceptionName = $_POST['exceptionName'];
  $functionName = $_POST['functionName'];
  $priority = $_POST['priority'];
  try {
    $exists = $wpdb->get_results("SELECT * from wp_opti_logs where 
    cast(date as date) = cast(now() as date) 
    and message = '$message'
    and exception = '$exception'
    and exceptionName = '$exceptionName'
    and functionName = '$functionName'");
    if(count($exists) == 0){
      $wpdb->query("INSERT INTO wp_opti_logs (message, exception, date, exceptionName, functionName, priority) VALUES ('$message', '$exception', now(), '$exceptionName', '$functionName', $priority);");
      $result = array();
      $result['logSalvo'] = true;
      return wp_send_json_success($result);
    }
      
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

add_action('wp_ajax_nopriv_get_infor_opti_ids', 'get_infor_opti_ids');
add_action('wp_ajax_get_infor_opti_ids', 'get_infor_opti_ids');
function get_infor_opti_ids(){
  try {
    global $wpdb;

    $id_blog = $wpdb->get_results("SELECT `value` AS `id_blog` FROM wp_opti_configurations WHERE name like '%idUsuarioWordpress%'");
    $id_user = $wpdb->get_results("SELECT `value` AS `id_user` FROM wp_opti_configurations WHERE name like '%idUsuarioOpti%'");

    $result['id_blog'] = !empty($id_blog) ? $id_blog[0]->id_blog : '0';
    $result['id_user'] = !empty($id_user) ? $id_user[0]->id_user : '0';
    return wp_send_json_success($result);
  }
  catch(Exception $e){
    echo $e->getMessage();
    save_log_php($e);
  }
}

//salvar logs do banco
function save_log_php($e) {
  global $wpdb;

  $message = $e->getMessage();
  $exception = $e->getTrace();
  $exceptionName = get_class($e);
  $functionName = $e->getLine();
  $priority = 1;
  try {
    $table_name = $wpdb->prefix . 'opti_logs';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
      $wpdb->query("INSERT INTO wp_opti_logs (message, exception, date, exceptionName, functionName, priority) VALUES ('$message', '$exception', now(), '$exceptionName', '$functionName', $priority);");
    }
      
  }
  catch(Exception $e){
    echo $e->getMessage();
  }
}

?>