<?php
/* ----------------------------------------------------------------------
   $Id: VariationWert.php,v 1.10 2006/07/13 03:41:08 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: VariationWert.php,v 1.0 16.06.06
   ----------------------------------------------------------------------

   eazySales Connector
   http://www.jtl-software.de/eazysales.php

   Copyright (c) 2006, JTL-Software
   ----------------------------------------------------------------------
   Original Author of file:  JTL-Software <thomas@jtl-software.de>
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */


/**
 * eazySales_Connector/dbeS/VariationsWert.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

$return = 3;
if (auth())
{
	if (intval($_POST["action"]) == 1 && intval($_POST['KeyEigenschaftWert']))
	{
		$return = 0;
		
		$EigenschaftWert->kEigenschaftWert = intval($_POST["KeyEigenschaftWert"]);
		$EigenschaftWert->kEigenschaft = intval($_POST["KeyEigenschaft"]);
		$EigenschaftWert->fAufpreis = floatval($_POST["Aufpreis"]);
		$EigenschaftWert->cName = realEscape($_POST["Name"]);

		//hole einstellungen
                $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
		$cur_query = xtc_db_query("SELECT languages_id, tax_class_id, tax_zone_id 
                                           FROM $eazysales_einstellungenstable");

		$einstellungen = mysql_fetch_object($cur_query);
		
		$products_options_id = getFremdEigenschaft($EigenschaftWert->kEigenschaft);
		if ($products_options_id>0)
		{
			//schaue, ob dieser EigenschaftsWert bereits global existiert fr diese Eigenschaft!!
                  $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
                  $products_options_valuestable = $oostable['products_options_values'];
			$cur_query = xtc_db_query("SELECT products_options_values.products_options_values_id
                                                   FROM $products_options_valuestable,
                                                        $products_options_values_to_products_optionstable
                                                   WHERE products_options_values_to_products_options.products_options_id=$products_options_id 
                                                     AND products_options_values_to_products_options.products_options_values_id=products_options_values.products_options_values_id 
                                                     AND products_options_values.language_id=$einstellungen->languages_id 
                                                     AND products_options_values.products_options_values_name=\"$EigenschaftWert->cName\"");
			$options_values = mysql_fetch_object($cur_query);
			
			if (!$options_values->products_options_values_id)
			{
				//erstelle diesen Wert global
				//hole max PK
                          $products_options_valuestable = $oostable['products_options_values'];
				$cur_query = xtc_db_query("SELECT max(products_options_values_id) 
                                                           FROM $products_options_valuestable");
				$max_id_arr = mysql_fetch_row($cur_query);
				$options_values->products_options_values_id = $max_id_arr[0]+1;

                          $products_options_valuestable = $oostable['products_options_values'];
				xtc_db_query("INSERT INTO $products_options_valuestable (products_options_values_id,language_id,products_options_values_name) VALUES ($options_values->products_options_values_id,$einstellungen->languages_id,\"$EigenschaftWert->cName\")");			
				
				//erstelle verknpfung zwischen wert und eig
                          $products_options_values_to_products_optionstable = $oostable['products_options_values_to_products_options'];
				xtc_db_query("INSERT INTO $products_options_values_to_products_optionstable (products_options_id,products_options_values_id) values($products_options_id,$options_values->products_options_values_id)");
			}
		
			//erstelle product_attribute
			$kArtikel = getEigenschaftsArtikel($EigenschaftWert->kEigenschaft);
			if ($kArtikel>0)
			{
				$products_id = getFremdArtikel($kArtikel);
				if ($products_id>0)
				{
					//hole products_tax_class_id
                                  $productstable = $oostable['products'];
					$cur_query = xtc_db_query("SELECT products_tax_class_id FROM $productstable WHERE products_id=".$products_id);
					$cur_tax = mysql_fetch_object($cur_query);
					$Aufpreis = ($EigenschaftWert->fAufpreis/(100+get_tax($cur_tax->products_tax_class_id)))*100;

                                  $products_attributestable = $oostable['products_attributes'];
					xtc_db_query("INSERT INTO $products_attributestable (products_id,options_id,options_values_id,options_values_price,price_prefix) values($products_id,$products_options_id,$options_values->products_options_values_id,$Aufpreis,\"+\")");
					$query = xtc_db_query("SELECT LAST_INSERT_ID()");
					$last_attribute_id_arr = mysql_fetch_row($query);					
					setMappingEigenschaftsWert($EigenschaftWert->kEigenschaftWert, $last_attribute_id_arr[0], $kArtikel);
				}
			}
		}
 	}
	else
		$return = 5;
}

echo($return);
logge($return);
?>