<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 005 05.04.16
 * Time: 0:31
 */

$del_icon = RNSFL_URL_PATH . "img/delete-icon.png";
wp_enqueue_style(RNSFL_NAME . '-admin-style', RNSFL_URL_PATH . 'style/' . RNSFL_NAME . '-admin.css');
wp_enqueue_script(RNSFL_NAME . '-admin-script', RNSFL_URL_PATH . 'scripts/admin.js', array('jquery'), null, true);
wp_localize_script(RNSFL_NAME . '-admin-script', 'rnsflDelIcon', $del_icon );
$data_prices = $wpdb->get_results("SELECT * FROM {$sql_table_name} ");
echo "<h2>rnsfl-calc</h2>";
echo "<p>Плагин для калькуляции стоимости услуг на сайте. Используйте шорткод <strong>[rnsfl_calc]</p>";
echo " <table style = 'sticky-thead' id = 'rnsfl_table'> ";
echo "<thead><tr><th>id</th><th>Название</th><th> Изготовление трафарет</th><th>Нанесение до 20 шт .</th><th> Нанесение 20 - 100 шт .</th><th> От 100 шт .</th><th></th></tr></thead><tbody>";
foreach ($data_prices as $value) {
    echo "<tr>";
    foreach ($value as $key => $under_value) {
        if ($key <> "id") {
            echo " <td contenteditable = \"true\">" . $under_value . "</td>";
        } else {
    echo "<td>" . $under_value . "</td>";
}
    }
    echo "<td><img class=\"del_icon\" src=\"" . $del_icon . "\"/></td>";
    echo "</tr>";
};
echo "</tbody></table>";
$data_prices_add = $wpdb->get_results("SELECT * FROM {$sql_table_additional_data} ");
foreach ($data_prices_add as $key=>$value) {
    switch ($value->key_t) {
        case "design_price":
            echo "<div class='lined'><label for=\"frb1\">Дизайн макета:</label><input name=\"frb1\" id=\"frb1\" type=\"text\" value='{$value->value}'/></div>";
            break;
        case "background_fill":
            echo "<div class='lined'><label for=\"frb2\">Заливка Фона:</label><input name=\"frb2\" id=\"frb2\" type=\"text\" value='{$value->value}'/></div>";
            break;
        case "color_price":
            echo "<div class='lined'><label for=\"color_price\">Ценовой шаг каждого цвета:</label><input name=\"color_price\" id=\"color_price\" type=\"text\" value='{$value->value}'/></div>";
            break;
        case "currency":
            echo "<div class='lined'><label for=\"currency\">Валюта:</label><input name=\"currency\" id=\"currency\" type=\"text\" value='{$value->value}'/></div>";
            break;
    }
}
echo "<br/>";
echo "<button type='button' class='button' id=\"rnsfl_add_row\">Добавить строку</button>";
echo "<button type='button' class='button' id=\"rnsfl_submit\">Отправить данные</button>";
echo "<button type='button' class='button' onClick='location.reload(false)'>Отмена</button>";
$path_to_gif = RNSFL_URL_PATH.'img/ajax-loader.gif';

$modal_style = "background: rgba( 255, 255, 255, .8 ) url({$path_to_gif}) 50% 50% no-repeat;";
echo "<div class=\"modal\" style='{$modal_style}'></div>";