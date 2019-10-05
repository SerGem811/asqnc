<?php
require_once 'config.php';
$db;
if ( !isset($db))
{
	include_once("Pdodb.class.php");
	//MySQL
	$db = new Pdodb(DB_DSN, DB_USER ,DB_PASSWORD );
}
   
function getprojects()
{
    global $db;
    $sth = $db->pdo->prepare("SELECT P.* , D.linadenegocio,
    							NP.nombre AS payment_plan_name
    							FROM proyectoa001 AS P
     							LEFT OUTER JOIN datosmaestros AS D ON D.id_datosmaestros = P.idlineadeneg AND D.conproyecto = 1
     							LEFT OUTER JOIN nameplanpago AS NP ON NP.id_nameplanpago = P.planpago
    						 ORDER BY nombreproy");
    $projects = array();
    if ($sth->execute()) 
    {
        $projects = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    return $projects;
}
function get_cuentas()
{
    global $db;
    $sth = $db->pdo->prepare("SELECT * FROM cuentas");
    $cuentas = array();
    if ($sth->execute()) 
    {
        $cuentas = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    return $cuentas;
}
function get_providers()
{
    global $db;
    $sth = $db->pdo->prepare("SELECT * FROM proveedoresa001");
    $ret = array();
    if ($sth->execute()) 
    {
        $ret = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    return $ret;
}
function getExtension($str) 
{
         $i = strrpos($str,".");
         if (!$i) { return ""; } 
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
 }
 
?>