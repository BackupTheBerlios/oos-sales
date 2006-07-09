<?php
/* ----------------------------------------------------------------------
   $Id: admininclude.php,v 1.3 2006/07/09 01:55:15 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: admininclude.php,v 1.0 16.06.06
   ----------------------------------------------------------------------

   eazySales_Connector
   http://www.jtl-software.de/eazysales.php

   Copyright (c) 2006, JTL-Software
   ----------------------------------------------------------------------
   Original Author of file:  JTL-Software <thomas@jtl-software.de>
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

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

require '../paths.php';
require_once("AdminSession.php");

//get DB Connecion
// Set the local configuration parameters - mainly for developers
if (file_exists(DOCROOT_XTC_PATH.'includes/local/configure.php')) 
	include(DOCROOT_XTC_PATH.'includes/local/configure.php');
// include server parameters
require_once (DOCROOT_XTC_PATH.'includes/configure.php');
require_once (DIR_FS_INC . 'xtc_db_connect.inc.php');
require_once (DIR_FS_INC . 'xtc_db_query.inc.php');

xtc_db_connect() or die('Kann Datenbankverbindung nicht herstellen! �erprfen Sie den DOCROOT_XTC_PATH im eazySales_Connector/paths.php Script Zeile 15. Der Pfad muss entweder relativ oder absolut auf das Rootverzeichnis Ihres Shops zeigen (meist <i>xtcommerce</i>).');

function eS_execute_query($query)
{	
	return xtc_db_query($query);
}

/**
 * real mysql escape mysql escape
 * @access public
 * @param string $ausdruck Ausdruck, der escaped fr mysql werden soll
 * @return escaped expression
 */
function realEscape ($ausdruck)
{
	if (get_magic_quotes_gpc())
		return mysql_real_escape_string(stripslashes($ausdruck));
	else
		return mysql_real_escape_string($ausdruck);
}