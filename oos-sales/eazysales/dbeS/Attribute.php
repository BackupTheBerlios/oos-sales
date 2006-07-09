<?php
/* ----------------------------------------------------------------------
   $Id: Attribute.php,v 1.6 2006/07/09 03:29:07 r23 Exp $

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


echo($return);
logge($return);

//Attribut wird verarbeitet / in DB insertet
function attributBearbeiten ($Attribut)
{
	if ($Attribut->content && $Attribut->products_id>0)
	{
		//hole einstellungen
		$cur_query = xtc_db_query("SELECT * FROM eazysales_einstellungen");
		$einstellungen = mysql_fetch_object($cur_query);
		
		switch (strtolower($Attribut->name))
		{
			case 'reihung':
				xtc_db_query("update products set products_sort=".intval($Attribut->content)." WHERE products_id=".$Attribut->products_id);
				break;
			case 'reihung startseite':
				xtc_db_query("update products set products_startpage_sort=".intval($Attribut->content)." WHERE products_id=".$Attribut->products_id);
				break;
			case 'suchbegriffe':
				xtc_db_query("update products_description set products_keywords=\"".realEscape($Attribut->content)."\" WHERE language_id=".$einstellungen->languages_id." AND products_id=".$Attribut->products_id);
				break;
			case 'meta title':
				xtc_db_query("update products_description set products_meta_title=\"".realEscape($Attribut->content)."\" WHERE language_id=".$einstellungen->languages_id." AND products_id=".$Attribut->products_id);
				break;
			case 'meta description':
				xtc_db_query("update products_description set products_meta_description=\"".realEscape($Attribut->content)."\" WHERE language_id=".$einstellungen->languages_id." AND products_id=".$Attribut->products_id);
				break;
			case 'meta keywords':
				xtc_db_query("update products_description set products_meta_keywords=\"".realEscape($Attribut->content)."\" WHERE language_id=".$einstellungen->languages_id." AND products_id=".$Attribut->products_id);
				break;
			case 'herstellerlink':
				xtc_db_query("update products_description set products_url=\"".realEscape($Attribut->content)."\" WHERE language_id=".$einstellungen->languages_id." AND products_id=".$Attribut->products_id);
				break;
			case 'lieferstatus':
				$shipping_id=0;
				//gibt es schon so einen shipping status?
				$cur_query = xtc_db_query("SELECT shipping_status_id FROM shipping_status WHERE language_id=".$einstellungen->languages_id." AND shipping_status_name=\"".realEscape($Attribut->content)."\"");
				$shipping_status_id_arr = mysql_fetch_row($cur_query);
				if ($shipping_status_id_arr[0]>0)
				{
					$shipping_id=$shipping_status_id_arr[0];
				}
				else 
				{
					//fge neuen Shippingstatus ein
					$cur_query = xtc_db_query("SELECT max(shipping_status_id) FROM shipping_status");
					$max_shipping_status_id_arr = mysql_fetch_row($cur_query);
					$shipping_id = $max_shipping_status_id_arr[0]+1;
					xtc_db_query("INSERT INTO shipping_status (shipping_status_id, language_id, shipping_status_name) values ($shipping_id, $einstellungen->languages_id, \"$Attribut->content\")");
				}
				xtc_db_query("update products set products_shippingtime=".$shipping_id." WHERE products_id=".$Attribut->products_id);
				break;
			case 'fsk 18':
				if ($Attribut->content=="ja")
				{
					xtc_db_query("update products set products_fsk18=1 WHERE products_id=".$Attribut->products_id);
				}
				break;
			case 'vpe wert':
				xtc_db_query("update products set products_vpe_value=".floatval($Attribut->content)." WHERE products_id=".$Attribut->products_id);
				break;
			case 'vpe anzeigen':
				if ($Attribut->content=="ja")
				{
					xtc_db_query("update products set products_vpe_status=1 WHERE products_id=".$Attribut->products_id);
				}
				elseif ($Attribut->content=="nein") 
				{
					xtc_db_query("update products set products_vpe_status=0 WHERE products_id=".$Attribut->products_id);
				}
				break;
		}
	}
}
?>