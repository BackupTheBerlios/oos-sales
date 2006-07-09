<?php
/* ----------------------------------------------------------------------
   $Id: index.php,v 1.4 2006/07/09 02:20:22 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: index.php,v 1.0 14.06.06
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
 * eazySales_Connector/index.php
 * AdminLogin fr eazySales Connector
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 14.06.06
*/

require_once("admininclude.php");
require_once("adminTemplates.php");

$adminsession = new AdminSession();

//adminlogin
if (intval($_POST["adminlogin"])==1)
{
	$user_query = xtc_db_query("SELECT * FROM customers WHERE customers_email_address=\"".realEscape($_POST["benutzer"])."\" AND customers_password=\"".md5($_POST["passwort"])."\"");
	$user = mysql_fetch_object($user_query);
	//hole DEFAULT_CUSTOMERS_STATUS_ID_ADMIN
	$cur_query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key=\"DEFAULT_CUSTOMERS_STATUS_ID_ADMIN\"");
	$def_adminstatus = mysql_fetch_object($cur_query);
	if ($user->customers_id>0 && $def_adminstatus->configuration_value==0)
	{
		$_SESSION["loggedIn"] = 1;
	}
}

zeigeKopf();
zeigeLinks($_SESSION["loggedIn"]);
if ($_SESSION["loggedIn"]!=1)
	zeigeLogin();
else
	zeigeLoginBereich();
zeigeFuss();

?>