<?php
/* ----------------------------------------------------------------------
   $Id: SetBestellung.php,v 1.2 2006/07/09 01:48:41 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: SetBestellung.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/SetBestellung.php
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
	$return=5;
	//Bestellung versandt
	if (intval($_POST["action"]) == 6 && intval($_POST['KeyBestellung']))
	{
		$return = 0;
		//setze orders_status auf gewählte Option bei eS Versadnt
		//hole einstellungen
		$cur_query = eS_execute_query("select StatusVersendet from eazysales_einstellungen");
		$einstellungen = mysql_fetch_object($cur_query);
		
		//setze status der Bestellung
		if ($einstellungen->StatusVersendet>0)
		{
			eS_execute_query("update orders set orders_status=".$einstellungen->StatusVersendet." where orders_id=".intval($_POST['KeyBestellung']));
			//füge history hinzu
			$VersandInfo = realEscape($_POST["VersandInfo"]);
			$VersandDatum = realEscape($_POST["VersandDatum"]);
			$Tracking = realEscape($_POST["Tracking"]);
			$kommentar = "Bestellung aus eazySales am $VersandDatum versandt.\n".$VersandInfo."\nIdentCode".$Tracking;
			eS_execute_query("insert into orders_status_history (orders_id, orders_status_id, date_added, comments) values(".intval($_POST['KeyBestellung']).", ".$einstellungen->StatusVersendet.", now(), \"".$kommentar."\")");
		}
 	}

	//Bestellung erfolgreich abgeholt
	if (intval($_POST["action"]) == 5 && intval($_POST['KeyBestellung']))
	{
		$return = 0;
		//setze orders_status auf gewählte Option bei eS Abholung
		//hole einstellungen
		$cur_query = eS_execute_query("select StatusAbgeholt from eazysales_einstellungen");
		$einstellungen = mysql_fetch_object($cur_query);
		
		//setze status der Bestellung
		if ($einstellungen->StatusAbgeholt>0)
		{
			eS_execute_query("update orders set orders_status=".$einstellungen->StatusAbgeholt." where orders_id=".intval($_POST['KeyBestellung']));
			//füge history hinzu
			$kommentar = "Erfolgreich in eazySales übernommen";
			eS_execute_query("insert into orders_status_history (orders_id, orders_status_id, date_added, comments) values(".intval($_POST['KeyBestellung']).", ".$einstellungen->StatusAbgeholt.", now(), \"".$kommentar."\")");
		}
		
		//setze bestellung auf abgeholt
		eS_execute_query("insert into eazysales_sentorders (orders_id, dGesendet) values (".intval($_POST['KeyBestellung']).",now())");
	}
}

mysql_close();
echo($return);
logge($return);
?>