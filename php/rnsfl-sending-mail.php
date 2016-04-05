<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 005 05.04.16
 * Time: 17:08
 */
function sending_mail()
{
    global $current_site;
    $mail = file_get_contents(RNSFL_PATH . 'html/email.html');
    $data = $_POST['data'];
    $comment_w = $data['comment'];
    if (!empty($comment_w)) {
        $comment_w = "<p>Заказчик оставил следующий комментарий</p><div style='border: 1px solid #0077CC; background-color: #fff; width: auto; padding: 8px 10px; box-shadow: 0 1px 2px rgba(34,36,38,0.1) inset;'><p>{$comment_w}</p></div>";
    } else {
        $comment_w = "<p>Заказчик не оставил комментариев</p>";
    }

    $sttr_replacing = array(
        "{CUSTOMER}" => $data['fio'],
        "{ADDR_EMAIL}" => $data['mail'],
        "{ADDR_PHONE}" => $data['phone'],
        "{COMMENT}" => $comment_w,
        "{SIZE}" => $data['size'], //Формат Имя
        "{DESIGN_PRICE}" => $data['design_price'],//Дизайн Макета
        "{COLORS}" => $data['colors'],
        "{BACKGROUND_FILL}" => $data['background_fill'],
        "{COUNT}" => $data['count'],
        "{FINAL_COST1}" => $data['final_cost1'],
        "{FINAL_COST2}" => $data['final_cost2'],
        "{FINAL_COST3}" => $data['final_cost3'],
        "{SITE_URL}" => site_url()
    );
    $mail = strtr($mail, $sttr_replacing);
    $subject = "Ordering on the website" . $current_site->site_name;
    $to = get_option('admin_email');
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    wp_mail($to, $subject, $mail, $headers);
    echo "Операция успешно завершена";
    wp_die();
}