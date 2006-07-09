<?php
/* ----------------------------------------------------------------------
   $Id: Variation.php,v 1.7 2006/07/09 14:23:23 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: Variation.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/Variation.php
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

$return = 3;
if (auth())
{
	if (intval($_POST["action"]) == 1 && intval($_POST['KeyEigenschaft']))
	{		
		$Eigenschaft->kEigenschaft = intval($_POST["KeyEigenschaft"]);
		$Eigenschaft->kArtikel = intval($_POST["KeyArtikel"]);
		$Eigenschaft->cName = realEscape($_POST["Name"]);

		//hole products_id
		$products_id = getFremdArtikel($Eigenschaft->kArtikel);
		if ($products_id>0)
		{
			//hole einstellungen
			$cur_query = xtc_db_query("SELECT languages_id FROM eazysales_einstellungen");
			$einstellungen = mysql_fetch_object($cur_query);
			
			//hol products_options_id
                        $products_optionstable = $oostable['products_options'];
			$cur_query = xtc_db_query("SELECT products_options_id
                                                   FROM $products_optionstable
                                                   WHERE language_id=".$einstellungen->languages_id."
                                                     AND products_options_name=\"$Eigenschaft->cName\"");
			$options_id = mysql_fetch_object($cur_query);
			if (!$options_id->products_options_id)
			{
				//erstelle eigenschaft
				//hole max PK
                          $products_optionstable = $oostable['products_options'];
				$cur_query = xtc_db_query("SELECT max(products_options_id) FROM $products_optionstable");
				$max_id_arr = mysql_fetch_row($cur_query);
				$options_id->products_options_id = $max_id_arr[0]+1;

                          $products_optionstable = $oostable['products_options'];
				xtc_db_query("INSERT INTO $products_optionstable (products_options_id,language_id,products_options_name) values ($options_id->products_options_id,$einstellungen->languages_id,\"$Eigenschaft->cName\")");
			}
			//mapping zu variation 
			setMappingEigenschaft($Eigenschaft->kEigenschaft,$options_id->products_options_id,$Eigenschaft->kArtikel);
			$return = 0;
		}
 	}
	else
		$return = 5;
}

echo($return);
logge($return);
?>