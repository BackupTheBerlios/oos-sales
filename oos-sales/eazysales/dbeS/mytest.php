<?php
/* ----------------------------------------------------------------------
   $Id: mytest.php,v 1.3 2006/07/09 02:00:18 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: mytest.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/mytest.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

require 'syncinclude.php';

$return=3;
$cName = $_POST["uID"];
$cPass = $_POST["uPWD"];

$_POST["uID"]="*";
$_POST["uPWD"]="*";

$cur_query = eS_execute_query("select * from eazysales_sync");
$loginDaten = mysql_fetch_object($cur_query);
if ($cName == $loginDaten->cName && $cPass == $loginDaten->cPass)
	$return=0;

mysql_close();
echo($return.";XTC");
//echo($return);
logge($return);
?>
