<?php
/* ----------------------------------------------------------------------
   $Id: GetPosEigenschaft.php,v 1.11 2006/07/13 04:05:01 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetPosEigenschaft.php,v1.0  15.06.06
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
 * eazySales_Connector/dbeS/setArtikel.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 15.06.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

  $return = 3;

  if (auth()) {

	$return = 5;
	if (intval($_POST['KeyBestellPos']))
	{		
		$return = 0;
		//hole einstellungen
                $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
		$cur_query = xtc_db_query("SELECT languages_id FROM $eazysales_einstellungenstable");
		$einstellungen = mysql_fetch_object($cur_query);

		//hole orders_products_id
		$orders_products_id = getFremdBestellPos(intval($_POST['KeyBestellPos']));
			
		//hole alle Eigenschaften, die ausgew�lt wurden zu dieser bestellung
                $orders_productstable = $oostable['orders_products'];
                $orders_products_attributestable = $oostable['orders_products_attributes'];
		$cur_query = xtc_db_query("SELECT orders_products_attributes.*, orders_products.products_tax, orders_products.products_id
                                           FROM $orders_products_attributestable,
                                                $orders_productstable
                                           WHERE orders_products_attributes.orders_products_id=".$orders_products_id."
                                             AND orders_products.orders_products_id=orders_products_attributes.orders_products_id
                                           ORDER BY orders_products_attributes.orders_products_id");
		while ($WarenkorbPosEigenschaft = mysql_fetch_object($cur_query))
		{
			$preisprefix=1;
			if ($WarenkorbPosEigenschaft->price_prefix=="-")
				$preisprefix=-1;

			//hole attribut
                  $products_attributestable = $oostable['products_attributes'];
                  $products_optionstable = $oostable['products_options'];
                  $products_options_valuestable = $oostable['products_options_values'];
			$attribut_query = xtc_db_query("SELECT products_attributes.products_attributes_id 
                                                        FROM $products_attributestable,
                                                             $products_optionstable,
                                                             $products_options_valuestable
                                                        WHERE products_attributes.products_id=".$WarenkorbPosEigenschaft->products_id." 
                                                          AND products_attributes.options_id=products_options.products_options_id 
                                                          AND products_attributes.options_values_id=products_options_values.products_options_values_id 
                                                          AND products_attributes.options_values_price=".$WarenkorbPosEigenschaft->options_values_price." 
                                                          AND products_options.products_options_name=\"".$WarenkorbPosEigenschaft->products_options."\" 
                                                          AND products_options.language_id=".$einstellungen->languages_id." 
                                                          AND products_options_values.products_options_values_name=\"".$WarenkorbPosEigenschaft->products_options_values."\" 
                                                          AND products_options_values.language_id=".$einstellungen->languages_id);
			
			$attribut_arr = mysql_fetch_row($attribut_query);
			
			echo(CSVkonform($WarenkorbPosEigenschaft->orders_products_attributes_id).';');
			echo(CSVkonform(intval($_POST['KeyBestellPos'])).';');
			echo(';');
			echo(CSVkonform(getEsEigenschaftsWert($attribut_arr[0],getEsArtikel($WarenkorbPosEigenschaft->products_id))).';');
			echo(CSVkonform(($WarenkorbPosEigenschaft->options_values_price+$WarenkorbPosEigenschaft->options_values_price*$WarenkorbPosEigenschaft->products_tax/100)*$preisprefix).';');
			echo("\n");
		}
	}
}

  echo($return);
  logge($return);
?>