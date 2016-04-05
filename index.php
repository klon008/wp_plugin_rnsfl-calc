<?php
/*
Plugin Name: rnsfl-calc
Plugin URI: http://kwork.ru/user/klon_008
Description: Плагин для калькуляции стоимости услуг на сайте. Используйте шорткод <strong>[rnsfl_calc]</strong>
Version: 1.0
Author: Селетков Павел
Author URI: http://kwork.ru/user/klon_008
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
define('RNSFL_PATH', plugin_dir_path(__FILE__));
define('RNSFL_URL_PATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');
define('RNSFL_NAME', "rnsfl-calc");
define("RNSFL_VERSION", "1.0");
$sql_table_pattern = strtolower($wpdb->prefix . str_replace("-", "_", RNSFL_NAME));
$sql_table_name = $sql_table_pattern . '_table';
$sql_table_additional_data = $sql_table_pattern . '_keys_table';
/**
 * Получение html для шорткода
 * @return string
 */
function rnsfl_get_calc()
{
    $res = file_get_contents(RNSFL_PATH . 'html/content.html');
    $res = str_replace("id=\"", "id=\"" . RNSFL_NAME . "-", $res);
    return $res;
}

/**
 * Хук для шорткода
 * @param $atts
 * @param null $content
 * @return string
 */
function rnsfl_insert_calc($atts, $content = null)
{
    global $sql_table_name;
    global $sql_table_additional_data;
    global $wpdb;
    $path_to_gif = RNSFL_URL_PATH.'img/ajax-loader.gif';
    $modal_style = "background: url({$path_to_gif}) 50% 50% no-repeat;";
    $data_prices = $wpdb->get_results("SELECT * FROM {$sql_table_name} ");
    $data_prices = json_encode($data_prices);
    $additional_data = $wpdb->get_results("SELECT * FROM {$sql_table_additional_data} ");
    $additional_data = json_encode($additional_data);

    wp_enqueue_script('accounting-script', RNSFL_URL_PATH . 'scripts/accounting.min.js', array(), '0.4.2', true);
    wp_enqueue_script(RNSFL_NAME . '-script', RNSFL_URL_PATH . 'scripts/' . RNSFL_NAME . '-script.js', array('jquery', 'accounting-script'), null, true);

    wp_localize_script(RNSFL_NAME . '-script', 'rnsfl_ajax', array('url' => admin_url('admin-ajax.php')));
    wp_localize_script(RNSFL_NAME . '-script', 'rnsflCalculatorPluginPrefix', RNSFL_NAME);
    wp_localize_script(RNSFL_NAME . '-script', 'rnsflDataPrices', $data_prices);
    wp_localize_script(RNSFL_NAME . '-script', 'rnsflAdditionalValues', $additional_data);
    wp_localize_script(RNSFL_NAME . '-script', 'rnsflModalStyle', $modal_style);

    wp_enqueue_style(RNSFL_NAME . '-style', RNSFL_URL_PATH . 'style/' . RNSFL_NAME . '-style.css');
    $slider = rnsfl_get_calc();
    $admin_email = get_option('admin_email');
    return $slider;
}

/*Добавление шорткода и установка ему хука*/
add_shortcode('rnsfl_calc', 'rnsfl_insert_calc');

include RNSFL_PATH . 'php/rnsfl-calc-bd-initialization.php';
/**
 * Оказывается внутри хука нет видимости к Global!
 * Активируем все в хук по-новой и хардкодим данные таблиц+переменные плагина
 */
function rnsfl_calc_plugin_init()
{
    global $wpdb;
    $sql_table_pattern = strtolower($wpdb->prefix . "rnsfl_calc");
    $sql_table_name = $sql_table_pattern . '_table';
    $sql_table_additional_data = $sql_table_pattern . '_keys_table';
    rnsfl_calc_plugin_install($sql_table_name, $sql_table_additional_data);
}
register_activation_hook(__FILE__, 'rnsfl_calc_plugin_init');

/**
 * Добавление подменю в админку
 * Плагины->rnsfl-calc
 */
function setup_theme_admin_menus(){
    add_submenu_page('plugins.php', 'Управление rnsfl-calc', 'Rnsfl-calculation', 'manage_options', RNSFL_PATH.'php/admin-page.php');
}
add_action("admin_menu", "setup_theme_admin_menus");

/**
 * Функция на изменение переменных
 */
function rnsfl_update_table_function(){
    //DO whatever you want with data posted
    //To send back a response you have to echo the result!
    $to_insert_data = $_POST['new_data'];
    $to_remove_data = $_POST['removed_data'];
    global $wpdb;
    global $sql_table_name;
    global $sql_table_additional_data;
/*    foreach ($to_remove_data as $row){

    }*/

    if (isset($to_remove_data)) {
        foreach ($to_remove_data as $key => $row) {
            $wpdb->delete(
                $sql_table_name,
                array('id'=>(int)$row),
                array("%d")
            );

        }
    }
    if (isset($to_insert_data)) {
        foreach ($to_insert_data as $key => $row) {
            $id = $row['id'];
            if ($id === "null"){
                $wpdb->insert(
                    $sql_table_name,
                    array(
                        'name'=>$row["name"],
                        'stencil_price'=>(float)$row["stencil_price"],
                        'applic_price_20'=>(float)$row["applic_price_20"],
                        'applic_price_100'=>(float)$row["applic_price_100"],
                        'applic_price_z'=>(float)$row["applic_price_z"]
                    ),
                    array(
                        '%s',
                        '%f',
                        '%f',
                        '%f',
                        '%f'
                    )
                );
            } else if ((int)$id > 0){
                $wpdb->update(
                    $sql_table_name,
                    array(
                        'name'=>$row["name"],
                        'stencil_price'=>(float)$row["stencil_price"],
                        'applic_price_20'=>(float)$row["applic_price_20"],
                        'applic_price_100'=>(float)$row["applic_price_100"],
                        'applic_price_z'=>(float)$row["applic_price_z"]
                    ),
                    array( 'id' => $id ),
                    array(
                        '%s',
                        '%f',
                        '%f',
                        '%f',
                        '%f'
                    ),
                    array('%d') );
            }
        }
    }
    /*
    var_dump($to_remove_data);
    var_dump($to_insert_data);*/
    $additional_data = $_POST['additional_data'];
    foreach ($additional_data as $key => $value){
        $wpdb->update(
            $sql_table_additional_data,
            array(
                'value'=> $value
            ),
            array( 'key_t' => $key ),
            array(
                '%s'
            ),
            array('%s')
        );
    }
    echo "Result";
    wp_die(); // ajax call must die to avoid trailing 0 in your response
}
add_action('wp_ajax_rnsfl_update_table', 'rnsfl_update_table_function');

/**
 * Хук на отправку EMAIL админу
 */
include RNSFL_PATH . 'php/rnsfl-sending-mail.php';
add_action('wp_ajax_rnsfl_mail_action', 'sending_mail');
add_action('wp_ajax_nopriv_rnsfl_mail_action', 'sending_mail');