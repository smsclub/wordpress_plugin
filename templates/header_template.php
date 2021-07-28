<?php 
$loginName = !empty($login) ? $login : 'Не определен';
$getMoney = !empty($number) ? $number : 'Не определен';
?>

<div class="header-block">
    <div class="inform-user"><p>Логин: <span><?php echo esc_html($loginName); ?></span></p>
        <!-- <p>Имя пользователя: <span>Сергей</span></p> -->
        <p>Текущий баланс: <span><?php echo esc_html($getMoney); ?></span></p>
    </div>
</div>