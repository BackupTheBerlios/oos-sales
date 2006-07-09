<?php
/* ----------------------------------------------------------------------
   $Id: Attribute.php,v 1.3 2006/07/09 02:00:18 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: Attribute.php,v1.01  27.06.06
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
 * eazySales_Connector/dbeS/Attribute.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.01 / 27.06.06
*/

require 'syncinclude.php';

$return=3;
if (auth())
{
	if (intval($_POST["action"]) == 1 && intval($_POST['KeyAttribut']))
	{
		$return = 0;
		
		$Attribut->products_id = getFremdArtikel(intval($_POST["KeyArtikel"]));
		$Attribut->name = $_POST["Name"];
		$Attribut->content = $_POST["StringWert"];
		if (strlen($_POST["TextWert"])>0)
			$Attribut->content = $_POST["TextWert"];
		attributBearbeiten ($Attribut);
	}
}

mysql_close();
echo($return);
logge($return);

//Attribut wird verarbeitet / in DB insertet
function attributBearbeiten ($Attribut)
{
	if ($Attribut->content && $Attribut->products_id>0)
	{
		//hole einstellungen
		$cur_query = eS_execute_query("select * from eazysales_einstellungen");
		$einstellungen = mysql_fetch_object($cur_query);
		
		switch (strtolower($Attribut->name))
		{
			case 'reihung':
				eS_execute_query("update products set products_sort=".intval($Attribut->content)." where products_id=".$Attribut->products_id);
				break;
			case 'reihung startseite':
				eS_execute_query("update products set products_startpage_sort=".intval($Attribut->content)." where products_id=".$Attribut->products_id);
				break;
			case 'suchbegriffe':
				eS_execute_query("update products_description set products_keywords=\"".realEscape($Attribut->content)."\" where language_id=".$einstellungen->languages_id." and products_id=".$Attribut->products_id);
				break;
			case 'meta title':
				eS_execute_query("update products_description set products_meta_title=\"".realEscape($Attribut->content)."\" where language_id=".$einstellungen->languages_id." and products_id=".$Attribut->products_id);
				break;
			case 'meta description':
				eS_execute_query("update products_description set products_meta_description=\"".realEscape($Attribut->content)."\" where language_id=".$einstellungen->languages_id." and products_id=".$Attribut->products_id);
				break;
			case 'meta keywords':
				eS_execute_query("update products_description set products_meta_keywords=\"".realEscape($Attribut->content)."\" where language_id=".$einstellungen->languages_id." and products_id=".$Attribut->products_id);
				break;
			case 'herstellerlink':
				eS_execute_query("update products_description set products_url=\"".realEscape($Attribut->content)."\" where language_id=".$einstellungen->languages_id." and products_id=".$Attribut->products_id);
				break;
			case 'lieferstatus':
				$shipping_id=0;
				//gibt es schon so einen shipping status?
				$cur_query = eS_execute_query("select shipping_status_id from shipping_status where language_id=".$einstellungen->languages_id." and shipping_status_name=\"".realEscape($Attribut->content)."\"");
				$shipping_status_id_arr = mysql_fetch_row($cur_query);
				if ($shipping_status_id_arr[0]>0)
				{
					$shipping_id=$shipping_status_id_arr[0];
				}
				else 
				{
					//fge neuen Shippingstatus ein
					$cur_query = eS_execute_query("select max(shipping_status_id) from shipping_status");
					$max_shipping_status_id_arr = mysql_fetch_row($cur_query);
					$shipping_id = $max_shipping_status_id_arr[0]+1;
					eS_execute_query("insert into shipping_status (shipping_status_id, language_id, shipping_status_name) values ($shipping_id, $einstellungen->languages_id, \"$Attribut->content\")");
				}
				eS_execute_query("update products set products_shippingtime=".$shipping_id." where products_id=".$Attribut->products_id);
				break;
			case 'fsk 18':
				if ($Attribut->content=="ja")
				{
					eS_execute_query("update products set products_fsk18=1 where products_id=".$Attribut->products_id);
				}
				break;
			case 'vpe wert':
				eS_execute_query("update products set products_vpe_value=".floatval($Attribut->content)." where products_id=".$Attribut->products_id);
				break;
			case 'vpe anzeigen':
				if ($Attribut->content=="ja")
				{
					eS_execute_query("update products set products_vpe_status=1 where products_id=".$Attribut->products_id);
				}
				elseif ($Attribut->content=="nein") 
				{
					eS_execute_query("update products set products_vpe_status=0 where products_id=".$Attribut->products_id);
				}
				break;
		}
	}
}
?>