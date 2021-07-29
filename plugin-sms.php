<?php
/*
Plugin Name: SMS CLUB Messages
Description: Send SMS plugin
Author: SMS CLUB
Author URI: https://smsclub.mobi
Version: 4.7
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

use SmsclubApi\Services\ApiService;

define('TABLE_SMSCLUB_STATISTIC', smsclubGetDb()->prefix . 'smsclub_statistic');
define('TABLE_SMSCLUB_SETTINGS', smsclubGetDb()->prefix . 'smsclub_settings');
define('SMSCLUB_USER_PROFILE_URL', 'https://my.smsclub.mobi/profile');
define('SMSCLUB_SMS_REPORT_URL', 'https://my.smsclub.mobi/reports');

register_activation_hook(__FILE__, 'smsclub_message_plugin_activation');


/**
 * Get WP database instance
 * @return wpdb
 */
function smsclubGetDb() {
	global $wpdb;

	return $wpdb;
}

/**
 * Create table after activate plugin
 */
function smsclub_message_plugin_activation() {
	smsclub_create_plugin_database_stat();
	smsclub_create_plugin_database_settings();
}

/**
 * Create table for messages statistic
 */
function smsclub_create_plugin_database_stat(){
    $smsclub_stat = 'CREATE TABLE ' . TABLE_SMSCLUB_STATISTIC . ' (
	    message_id int(15) NOT NULL,
	    user_login bigint(15) NOT NULL,
	    message_type varchar(10) NOT NULL,
	    message_number varchar(20) NOT NULL,
	    sender_name varchar(15) NOT NULL,
	    message_text text NOT NULL,
	    status varchar(20) NOT NULL,
	    creation_date timestamp NOT NULL,
	    status_change_date timestamp NOT NULL
    )';

    smsclubGetDb()->query($smsclub_stat);
}

/**
 * Create table for plugin settings
 */
function smsclub_create_plugin_database_settings(){

    $smsclub_account_data = "CREATE TABLE " . TABLE_SMSCLUB_SETTINGS . " (
    login VARCHAR(15) NOT NULL,
    token VARCHAR(15) NOT NULL
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($smsclub_account_data);

}

add_action( 'admin_enqueue_scripts', 'smsclubStyleScriptPlugin');

function smsclubStyleScriptPlugin(){
    wp_enqueue_style( 'message-admin-style', plugin_dir_url( __FILE__ ) .'css/wp-admin.css' );
    wp_enqueue_script( 'message-admin-maskinput', plugin_dir_url( __FILE__ ) .'js/maskinput.js' );
    wp_enqueue_script( 'message-admin-plugin', plugin_dir_url( __FILE__ ) .'js/pluginSms.js' );

}


// Delete table when deactivate
// function deactivation_plugin_remove_database() {
//     $smsclub_stat = "DROP TABLE IF EXISTS ". TABLE_SMSCLUB_STATISTIC ;
//     smsclubGetDb()->query($smsclub_stat);

//     $smsclub_account_data = "DROP TABLE IF EXISTS " . TABLE_SMSCLUB_SETTINGS ;
//     smsclubGetDb()->query($smsclub_account_data);
//     delete_option("my_plugin_db_version");
// }
// register_deactivation_hook( __FILE__, 'deactivation_plugin_remove_database' );

// EnD


// Delete table when unistall
function delete_plugin_database_table(){
    $smsclub_stat = "DROP TABLE IF EXISTS ". TABLE_SMSCLUB_STATISTIC;
    smsclubGetDb()->query($smsclub_stat);

    $smsclub_account_data = "DROP TABLE IF EXISTS" . TABLE_SMSCLUB_SETTINGS;
	smsclubGetDb()->query($smsclub_account_data);
}

register_uninstall_hook(__FILE__, 'delete_plugin_database_table');

// EnD

function message_plugin_admin_menu(){
    add_menu_page( 'Message Plugin', 'Отправка СМС', 'manage_options', 'message_plugin', 'message_plugin_page', 'dashicons-clipboard' );
//    add_submenu_page( 'message_plugin', 'Send Viber Message', 'Отправка viber сообщения', 'manage_options', 'viber_message_slug', 'viber_message_page');
    add_submenu_page( 'message_plugin', 'Statistic', 'Статистика', 'manage_options', 'statistic', 'statistic_page');
    add_submenu_page( 'message_plugin', 'Settings', 'Настройки', 'manage_options', 'message_settings', 'settings_page');
}

add_action( 'admin_menu', 'message_plugin_admin_menu' );


class SmsClubAPI {

    private static $instances;

    protected function __construct() { }


    public static function getInstance() {
        if (!self::$instances) {
            self::$instances = new ApiService(ApiServiceData());
        }

        return self::$instances;
    }
}

function ApiServiceData() {
    $data = get_account_data();

	return ['token' => $data->token, 'login' => $data->login];
}

// Хэдэр страниц плагина
function headerPage() {

    require_once 'php-new-master/autoload.php';

    $api = SmsClubAPI::getInstance();
    if ( $api->getBalance() ) {
        $balance = $api->getBalance()->getMoney();
        $number = number_format($balance, 2);
        $login = ApiServiceData()['login'];
    }
    require('templates/header_template.php');
}

//Форма которая используется на страницах "Отправка СМС" и "Отправка вайбер"
function form_sms_viber($type) {

    require_once 'php-new-master/autoload.php';
    $api = SmsClubAPI::getInstance();
    
    if ( $type == "sms" ) {
        $title = 'СМС';
        $typeMessage="sms";
        $originators = $api->getSmsOriginators();
    }

    if ( $type == "viber" ) {
        $title = 'Viber';
        $typeMessage="viber";
        if ( $api->getErrors() ) {
            $originators = null;
        } else {
            $originators = $api->getViberOriginators();
        }
    }

    require('templates/form_sms_viber_template.php');
    
}

// Страница "Отправка СМС"
function message_plugin_page() {
    ob_start();

    $type = 'sms';

    if ( get_account_data() == null ) {

        require('templates/connect_account_template.php');
    } else {
        headerPage();

        form_sms_viber($type);

        require('templates/popup_message_inform_template.php');
        require('templates/spinner.php');

    }
}

//Обработка запроса с фронта и запись в базу данных
add_action('wp_ajax_send_message', 'send_message');
function send_message() {
    $message     = sanitize_textarea_field($_POST['message']);
    $phoneNumber = sanitize_text_field($_POST['phoneNumber']);
    $alphaName   = sanitize_text_field($_POST['alphaName']);
    $typeMessage = sanitize_text_field($_POST['typeMessage']);
    $loginUser = ApiServiceData()['login'];

    if ( empty($alphaName) ) {
        $errorSms = ['criticalError'];
        echo json_encode($errorSms);
        exit;
    }

    $today = date("Y-m-d H:i:s", strtotime("+2 hours")); 

    $arrayPhoneNumber = explode(',', $phoneNumber);

    require_once 'php-new-master/autoload.php';
    $api = SmsClubAPI::getInstance();

    if ( $typeMessage == 'sms' ) {
        $sms = new \SmsclubApi\Classes\Sms();
        $sms
            // Устанавливаем отправителя
            ->setOriginator(new \SmsclubApi\Classes\Originator($alphaName))
            // Устанавливаем номера получателей
            // Если получатель один, следует указывать один елемент массива
            ->setPhones($arrayPhoneNumber)
            // Устанавливаем текст сообщения
            ->setMessage($message);
        $result = $api->sendSms($sms);
    }

    if ( $typeMessage == 'viber' ) {
        $viberMessage = new \SmsclubApi\Classes\ViberMessage();
        $viberMessage
            // Устанавливаем отправителя
            ->setOriginator(new \SmsclubApi\Classes\Originator($alphaName))
            // Устанавливаем номера получателей
            // Если получатель один, следует указывать один елемент массива
            ->setPhones($arrayPhoneNumber)
            // Устанавливаем текст сообщения
            ->setMessage($message);

        $result = $api->sendViber($viberMessage);
    }

    if (!$result) {
        foreach($api->getErrors() as $error ) {
            // Получить сообщение ошибки
            $errorCode = $error->getCode();
            $errorMessage = $error->getMessage();
            $errorSms = ['error', $errorCode, $errorMessage];
            echo json_encode($errorSms);
            exit;
        }
    } else {
        foreach($result as $item) {
            // Получить ID сообщения от SMS Club
            $messageId = $item->getId();
            $arrayMessageId[] = $messageId;

            if ( $typeMessage == 'sms' ) {
                $resultStatuses = $api->getSmsStatuses($arrayMessageId);
            }

            if ( $typeMessage == 'viber' ) {
                $resultStatuses = $api->getViberStatuses($arrayMessageId);
            }

            if ( $resultStatuses ) {
                foreach($resultStatuses as $value) {
                    // Получить статус сообщения
                    $messageStatus = $value->getStatus();
                    break;
                }
            }

            // Получить номер на которой отправлено СМС
            $messageNumber = $item->getNumber();

            $smsclub_stat = "INSERT INTO " . TABLE_SMSCLUB_STATISTIC . " (
                `message_id`, `user_login`, `message_type`, `message_number`, `sender_name`,`message_text`, `status`, `creation_date`, `status_change_date` ) VALUES (
                '$messageId', '$loginUser', '$typeMessage', '$messageNumber', '$alphaName', '$message', '$messageStatus', '$today', '$today' ) ;";

	        smsclubGetDb()->query($smsclub_stat);
        }
        $successMessage = ['success', '200'];
        echo json_encode($successMessage);
    }
    exit;
}

//Страница отправка вайбера
function viber_message_page() {

    ob_start();
    $type = 'viber';

    if ( get_account_data() == null ) {
        require('templates/connect_account_template.php');
    } else {
        headerPage();

        form_sms_viber($type);

        require('templates/popup_message_inform_template.php');
        require('templates/spinner.php');

    }
}

// Страница настройки
function settings_page() {
    ob_start();

   if (false !== get_account_data()) {
         headerPage();
    }

    require('templates/settings_page_template.php');
    require('templates/spinner.php');
    require('templates/popup_message_inform_template.php');
}

// Из кириллицы в латиницу
function transliterateen($input){
    $gost = array(
        "a"=>"а",    "b"=>"б",    "v"=>"в",    "g"=>"г",    "d"=>"д",
        "e"=>"е",    "yo"=>"ё",   "j"=>"дж",   "z"=>"з",    "i"=>"и",
        "i"=>"й",    "k"=>"к",    "l"=>"л",    "m"=>"м",    "n"=>"н",
        "o"=>"о",    "p"=>"п",    "r"=>"р",    "s"=>"с",    "t"=>"т",
        "y"=>"у",    "f"=>"ф",    "h"=>"х",    "c"=>"ц",    "ch"=>"ч",
        "sh"=>"ш",   "tsch"=>"щ", "i"=>"ы",    "e"=>"е",    "u"=>"у",
        "ya"=>"я",   "w" => "в",  "x"=>"кс",
        
        "A"=>"А",    "B"=>"Б",    "V"=>"В",    "G"=>"Г",    "D"=>"Д",
        "E"=>"Е",    "Yo"=>"Ё",   "J"=>"ДЖ",   "Z"=>"З",    "I"=>"И",
        "I"=>"Й",    "K"=>"К",    "L"=>"Л",    "M"=>"М",    "N"=>"Н",
        "O"=>"О",    "P"=>"П",    "R"=>"Р",    "S"=>"С",    "T"=>"Т",
        "Y"=>"У",    "F"=>"Ф",    "H"=>"Х",    "C"=>"Ц",    "Ch"=>"Ч",
        "SH"=>"Ш",   "TSCH"=>"Щ", "I"=>"Ы",    "E"=>"Е",    "U"=>"У",
        "Ya"=>"Я",   "W" => "В",  "X"=>"КС",
        
        
        "'"=>"ь",    "'"=>"Ь",    "''"=>"ъ",   "''"=>"Ъ",   "i"=>"и",    
        "ye"=>"є",   "I"=>"І",    "G"=>"Г",    "YE"=>"Є",   "yi"=>"ї",
        "YI"=>"Ї",   "GG"=>"Ґ",   "gg"=>"ґ",   "zh"=>"ж",   "ZH"=>"Ж" 
    );
    return strtr($input, $gost);
}


//Из латиницы в кириллицу
function transliterate($input){
    $gost = array(
        "а"=>"a",    "б"=>"b",    "в"=>"v",    "г"=>"g",    "д"=>"d",
        "е"=>"e",    "ё"=>"yo",   "ж"=>"zh",   "з"=>"z",    "и"=>"i",
        "й"=>"i",    "к"=>"k",    "л"=>"l",    "м"=>"m",    "н"=>"n",
        "о"=>"o",    "п"=>"p",    "р"=>"r",    "с"=>"s",    "т"=>"t",
        "у"=>"y",    "ф"=>"f",    "х"=>"h",    "ц"=>"c",    "ч"=>"ch",
        "ш"=>"sh",   "щ"=>"tsch", "ы"=>"i",    "э"=>"e",    "ю"=>"u",
        "я"=>"ya",   "кс"=>"x",

        "А"=>"A",    "Б"=>"B",    "В"=>"V",    "Г"=>"G",    "Д"=>"D",
        "Е"=>"E",    "Ё"=>"Yo",   "Ж"=>"ZH",   "З"=>"Z",    "И"=>"I",
        "Й"=>"I",    "К"=>"K",    "Л"=>"L",    "М"=>"M",    "Н"=>"N",
        "О"=>"O",    "П"=>"P",    "Р"=>"R",    "С"=>"S",    "Т"=>"T",
        "У"=>"Y",    "Ф"=>"F",    "Х"=>"H",    "Ц"=>"C",    "Ч"=>"Ch",
        "Ш"=>"SH",   "Щ"=>"TSCH", "Ы"=>"I",    "Э"=>"E",    "Ю"=>"U",
        "Я"=>"Ya",   "КС"=>"X",

        "ь"=>"",     "Ь"=>"",     "ъ"=>"",     "Ъ"=>"",     "ї"=>"yi",
        "і"=>"i",    "ґ"=>"gg",   "є"=>"ye",   "Ї"=>"YI",   "І"=>"I",
        "Ґ"=>"GG",   "Є"=>"YE",   "дж"=>"j",   "ДЖ"=>"J"
    );
    return strtr($input, $gost);
}

add_action('wp_ajax_translit_message', 'translit_message');
function translit_message() {
	$message = sanitize_textarea_field($_POST['message']);
	$message = preg_match('/[a-z]/i', $message) ? transliterateen($message) : transliterate($message);

	echo esc_textarea($message);
}

add_action('wp_ajax_statistic_inform', 'statistic_inform');
function statistic_inform() {
    $adminUrl = 'admin.php?page=statistic&type_message=';
    $fullAdminUrl = get_admin_url().$adminUrl;
    $loginUser = ApiServiceData()['login'];


    if ( (!isset($_GET['type_message'])) || ($_GET['type_message'] == 'all') ) {
        $selecA = "selected='selected'";
        $selecV = $selecS = '';
        $smsclub_stat_inform = "SELECT * FROM ". smsclubGetDb()->dbname ."." . TABLE_SMSCLUB_STATISTIC . " WHERE `user_login` = ". $loginUser ."   ORDER BY `creation_date` DESC LIMIT 50";

    } 

    if ( (isset($_GET['type_message'])) && (($_GET['type_message'] == 'viber') || ($_GET['type_message'] == 'sms')) ) {
        if ($_GET['type_message'] == 'viber') {
            $selecV = "selected='selected'";
            $selecA = $selecS = '';
        }

        if ($_GET['type_message'] == 'sms') {
            $selecS = "selected='selected'";
            $selecA = $selecV = '';
        }
        $smsclub_stat_inform = "SELECT * FROM ". smsclubGetDb()->dbname ."." . TABLE_SMSCLUB_STATISTIC . " WHERE message_type='". $_GET['type_message'] ."' AND `user_login` = ". $loginUser ." ORDER BY `creation_date` DESC LIMIT 50";
    }

    if ( !isset($_GET['type_message'] )  ) {
        $selecA = $selecV = $selecS = '';
    }

    $results = [smsclubGetDb()->get_results( $smsclub_stat_inform ), $fullAdminUrl, $selecA, $selecV, $selecS] ;
    return $results;
}

// Страница статистика
function statistic_page() {
    $stat_informs = statistic_inform()[0];
    $stat_informs_url = statistic_inform()[1];
    $selecAll = statistic_inform()[2];
    $selecViber = statistic_inform()[3];
    $selecSms = statistic_inform()[4];


    if ( get_account_data() == null ) {
        require('templates/connect_account_template.php');
    } else {
        headerPage();

        require('templates/statistic_page_template.php');

        require('templates/spinner.php');

    }
}

/**
 * @return false|object
 */
function get_account_data() {
    $result = smsclubGetDb()->get_results('SELECT login, token FROM ' . TABLE_SMSCLUB_SETTINGS);

    if (empty($result)) {
	    return false;
    }

	return $result[0];
}

// Обработка запроса от страници "настройки" и запись в базу данных
add_action('wp_ajax_user_account_data', 'user_account_data');
function user_account_data() {
    $login    = sanitize_text_field($_POST['login']);
    $token    = sanitize_text_field($_POST['token']);

    if ( empty(get_account_data()) ) {
        $sql_data = "INSERT INTO " . TABLE_SMSCLUB_SETTINGS . " (
            `login`, `token` ) VALUES (
            '$login', '$token' ) ;";

    } else {
        $sql_data = "UPDATE " . TABLE_SMSCLUB_SETTINGS . " SET login='$login', token='$token' ;";
    }

	smsclubGetDb()->query($sql_data);

    exit;
}