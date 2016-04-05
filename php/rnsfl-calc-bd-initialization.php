<?php
/**
 * Заполнение БД тестовыми данными
 */
function rnsfl_install_data($table_name) {
    global $wpdb;

    $array_of_values = array(
        array(
            'name' => "А4 (210х297мм)",
            'stencil_price' => 700.00,
            'applic_price_20' =>166.00,
            'applic_price_100' => 144.00,
            'applic_price_z' => 130.00
        ),
        array(
            'name' => "А3 (297х420мм)",
            'stencil_price' => 800.00,
            'applic_price_20' =>216.00,
            'applic_price_100' => 187.00,
            'applic_price_z' => 173.00
        ),
        array(
            'name' => "А2 (420х594мм)",
            'stencil_price' => 900.00,
            'applic_price_20' => 259.00,
            'applic_price_100' => 230.00,
            'applic_price_z' => 216.00
        ),
        array(
            'name' => "А1 (594х841мм)",
            'stencil_price' => 1200.00,
            'applic_price_20' => 360.00,
            'applic_price_100' => 331.00,
            'applic_price_z' => 302.00
        ),
        array(
            'name' => "А0 (841х1189мм)",
            'stencil_price' => 1500.00,
            'applic_price_20' => 461.00,
            'applic_price_100' => 432.00,
            'applic_price_z' => 403.00
        )
    );

    foreach ($array_of_values as $value){
        $wpdb->insert(
            $table_name,
            $value,
            array(
                '%s',
                '%f',
                '%f',
                '%f',
                '%f'
            )
        );
    }
}

function rnsfl_install_additional_data($table_name){
    global $wpdb;
    $array_of_values = array(
        array(
            'key_t' => 'design_price',
            'value' => '600'
        ),
        array(
            'key_t' => 'background_fill',
            'value' => '40%'
        ),
        array(
            'key_t' => 'color_price',
            'value' => '30%'
        ),
        array(
            'key_t' => 'currency',
            'value' => 'Руб.'
        )
    );
    foreach ($array_of_values as $value){
        $wpdb->insert(
            $table_name,
            $value,
            array(
                '%s',
                '%s'
            )
        );
    }
}


/**
 * Инициализация плагина в бд
 */
function rnsfl_calc_plugin_install($sql_table_name,$sql_table_additional_data)
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    if (($wpdb->get_var("SHOW TABLES LIKE '{$sql_table_name}'")) !== $sql_table_name) {
        $sql = "CREATE TABLE  {$sql_table_name} (
            id INT(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            stencil_price DECIMAL(10, 2), 
            applic_price_20 DECIMAL(10, 2), 
            applic_price_100 DECIMAL(10, 2), 
            applic_price_z DECIMAL(10, 2),
            UNIQUE KEY id (id)) ${charset_collate}";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option(RNSFL_NAME . '-table', RNSFL_VERSION);

        rnsfl_install_data($sql_table_name);

    }
    if (($wpdb->get_var("SHOW TABLES LIKE '{$sql_table_additional_data}'")) !== $sql_table_additional_data){
        $sql = "CREATE TABLE  {$sql_table_additional_data} (
            key_t VARCHAR(100) NOT NULL, 
            value VARCHAR(255) NOT NULL,
             PRIMARY KEY (key_t)) ${charset_collate}";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option(RNSFL_NAME . '-table', RNSFL_VERSION);

        rnsfl_install_additional_data($sql_table_additional_data);
    }
}
