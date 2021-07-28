<?php 
$valueButton = empty(get_account_data()) ? 'Сохранить' : 'Обновить';
?>

<div class="form">
    <form id="form_settings">
        <h3 class="name-form">Настройки</h3>
        <div class="form-group">
            <div id="left-side">
                <div class="setting-login">
                    <label for="login">Логин</label>
                    <input type="text" class="login" name="login">
                    <p class="error login-error" hidden>*Поле Логин обязательно к заполнению</p>
                </div>
            </div>
            <div id="right-side">
                <div class="setting-token">
                    <label for="token">Токен </label>
                    <input id="token" class="token" name="token" type="text" >
                    <p class="error token-error" hidden>*Поле Токен обязательно к заполнению</p>
                </div>
                <p class="save"><input type="button" class="account-data" value="<?php echo esc_attr($valueButton); ?>"></p>
            </div>
        </div>
        <p style="padding-left:30px">Получить токен можно в <a href="<?php echo esc_url(SMSCLUB_USER_PROFILE_URL); ?>" target="_blank">личном кабинете Sms Club</a></p>
    </form>
</div>