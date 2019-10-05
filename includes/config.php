<?php
$HOST = preg_replace('/^www\./', '', $_SERVER['SERVER_NAME']);
switch (trim($HOST)) {

    case "localhost":
        define('DEBUG', true);

        break;
    case "tecriak.com":
        define('DEBUG', false);
        break;
    default:
        // live server
        define('DEBUG', false);
        break;
}


if (DEBUG == true) {
    define('DB_DSN', 'mysql:host=localhost;dbname=dbs_asqi_16');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');


} else {
    //Production
    define('DB_DSN', 'mysql:host=db5000111253.hosting-data.io;dbname=dbs105687');
    define('DB_USER', 'dbu164278');
    define('DB_PASSWORD', 'Wealthy20191!');

    /*define ('DB_DSN', 'mysql:host=localhost;dbname=mon_live');
    define ('DB_USER', 'mon_mania');
    define ('DB_PASSWORD', 'mania@2019');
    */

}

$ROOT_SITE = (DEBUG == TRUE) ? "http://{$_SERVER['SERVER_NAME']}" : "https://{$_SERVER['SERVER_NAME']}";
$SECURE_SITE = (DEBUG == TRUE) ? "http://{$_SERVER['SERVER_NAME']}" : "https://{$_SERVER['SERVER_NAME']}";
$BASE_FILEPATH = $_SERVER['DOCUMENT_ROOT'];

$SCRIPT_NAME = $_SERVER['SCRIPT_NAME'];
// The first part of the script path is
// a subdomain on the development server...
$pos = strpos($SCRIPT_NAME, "/", 1);
//echo $scriptName. $pos;

if ($pos) {
    $ROOT_SITE .= (substr($SCRIPT_NAME, 0, $pos));
    $BASE_FILEPATH .= substr($SCRIPT_NAME, 0, $pos);
}
define('BASE_URL', $ROOT_SITE);
define('SECURE_BASE_URL', $SECURE_SITE);
define('BASE_FILEPATH', $BASE_FILEPATH);

$db;
if (!isset($db)) {
    include_once("Pdodb.class.php");
    //MySQL
    $db = new Pdodb(DB_DSN, DB_USER, DB_PASSWORD);
}

$year;
if(!isset($year)) {
	$year = 2016;
}

?>