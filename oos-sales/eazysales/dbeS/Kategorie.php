<?php
/* ----------------------------------------------------------------------
   $Id: Kategorie.php,v 1.4 2006/07/09 02:07:11 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: Kategorie.php,v1.01  05.07.06
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
 * eazySales_Connector/dbeS/Kategorie.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.01 / 05.07.06
*/

require 'syncinclude.php';

$return=3;
if (auth())
{
	//hole einstellungen
	$cur_query = eS_execute_query("SELECT * FROM eazysales_einstellungen");
	$einstellungen = mysql_fetch_object($cur_query);

	if ((intval($_POST["action"]) == 1 || intval($_POST["action"]) == 3) && intval($_POST['KeyKategorie'])>0)
	{
		$return = 0;
		$Kategorie->kKategorie = intval($_POST["KeyKategorie"]);
		$Kategorie->kOberKategorie = intval($_POST["KeyOberKategorie"]);
		$Kategorie->cName = realEscape($_POST["KeyName"]);
		$Kategorie->cBeschreibung = realEscape(htmlentities($_POST["KeyBeschreibung"]));
		$Kategorie->parent_id = 0;
		
		if ($Kategorie->kOberKategorie>0)
		{
			//existiert oberkat?
			$categories_id_oberkat = getFremdKategorie($Kategorie->kOberKategorie);
			if (!$categories_id_oberkat)
			{
				eS_execute_query("insert into categories (categories_status, date_added, categories_template, listing_template, products_sorting, products_sorting2) values (0,now(),\"$einstellungen->cat_category_template\",\"$einstellungen->cat_listing_template\",\"$einstellungen->cat_sorting\",\"$einstellungen->cat_sorting2\")");
				//hole id
				$query = eS_execute_query("SELECT LAST_INSERT_ID()");
				$categories_id_oberkat_arr = mysql_fetch_row($query);
				eS_execute_query("insert into categories_description (categories_id, language_id) values (".$categories_id_oberkat_arr[0].",$einstellungen->languages_id)");
				$Kategorie->parent_id = $categories_id_oberkat_arr[0];
				setMappingKategorie($Kategorie->kOberKategorie, $Kategorie->parent_id);
			}
			else 
				$Kategorie->parent_id = $categories_id_oberkat;
		}
		//update oder insert?
		$categories_id = getFremdKategorie($_POST['KeyKategorie']);
		if ($categories_id>0)
		{
			//update
			eS_execute_query("update categories set parent_id=$Kategorie->parent_id, categories_status=1 WHERE categories_id=".$categories_id);
			eS_execute_query("update categories_description set categories_name=\"$Kategorie->cName\", categories_description=\"$Kategorie->cBeschreibung\" WHERE categories_id=".$categories_id." AND language_id=".$einstellungen->languages_id);
		}
		else 
		{
			//insert
			eS_execute_query("insert into categories (parent_id, categories_status, categories_template, listing_template, products_sorting, products_sorting2, date_added) values ($Kategorie->parent_id,1,\"".$einstellungen->cat_category_template."\",\"".$einstellungen->cat_listing_template."\",\"".$einstellungen->cat_sorting."\",\"".$einstellungen->cat_sorting2."\",now())");
			$query = eS_execute_query("SELECT LAST_INSERT_ID()");
			$categories_id_arr = mysql_fetch_row($query);
			eS_execute_query("insert into categories_description (categories_id, language_id, categories_name, categories_description) values (".$categories_id_arr[0].",$einstellungen->languages_id, \"$Kategorie->cName\", \"$Kategorie->cBeschreibung\")");
			setMappingKategorie($Kategorie->kKategorie, $categories_id_arr[0]);
		}
 	}	
 	
	if (intval($_POST["action"]) == 3 && intval($_POST['KeyKategorie'])>0)
	{
		$return=0;
		$cat = getFremdKategorie(intval($_POST['KeyKategorie']));
		if ($cat>0)
			eS_execute_query("update categories set categories_status=0 WHERE categories_id=".$cat);
	}
}

mysql_close();
echo($return);
logge($return);
?>