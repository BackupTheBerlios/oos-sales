<?php
/**
 * eazySales_Connector/dbeS/admininclude.php
 * 
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

require_once("../paths.php");
require_once("AdminSession.php");

//get DB Connecion
// Set the local configuration parameters - mainly for developers
if (file_exists(DOCROOT_XTC_PATH.'includes/local/configure.php')) 
	include(DOCROOT_XTC_PATH.'includes/local/configure.php');
// include server parameters
require_once (DOCROOT_XTC_PATH.'includes/configure.php');
require_once (DIR_FS_INC . 'xtc_db_connect.inc.php');
require_once (DIR_FS_INC . 'xtc_db_query.inc.php');

xtc_db_connect() or die('Kann Datenbankverbindung nicht herstellen! Überprüfen Sie den DOCROOT_XTC_PATH im eazySales_Connector/paths.php Script Zeile 15. Der Pfad muss entweder relativ oder absolut auf das Rootverzeichnis Ihres Shops zeigen (meist <i>xtcommerce</i>).');

function eS_execute_query($query)
{	
	return xtc_db_query($query);
}

/**
 * real mysql escape mysql escape
 * @access public
 * @param string $ausdruck Ausdruck, der escaped für mysql werden soll
 * @return escaped expression
 */
function realEscape ($ausdruck)
{
	if (get_magic_quotes_gpc())
		return mysql_real_escape_string(stripslashes($ausdruck));
	else
		return mysql_real_escape_string($ausdruck);
}