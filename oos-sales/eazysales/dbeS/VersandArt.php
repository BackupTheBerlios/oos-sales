<?php
/**
 * eazySales_Connector/dbeS/VersandArt.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

require_once("syncinclude.php");
$return=3;
if (auth())
{
	if (intval($_POST["action"]) == 1 && intval($_POST['KeyVersandArt']))
	{
		$return = 0;
 	}
	else
		$return=5;

	if (intval($_POST["action"]) == 3 && intval($_POST['KeyVersandArt']))
	{
		$return = 0;
	}
}

mysql_close();
echo($return);
//logge($return);
?>