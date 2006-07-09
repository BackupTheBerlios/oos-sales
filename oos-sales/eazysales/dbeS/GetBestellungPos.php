<?php
/* ----------------------------------------------------------------------
   $Id: GetBestellungPos.php,v 1.7 2006/07/09 15:42:44 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetBestellungPos.php,v1.0  15.06.06
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
 * eazySales_Connector/dbeS/GetBestellungPos.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 15.06.06
*/

require 'syncinclude.php';

$return=3;
if (auth())
{
	$return=5;
	if (intval($_POST['KeyBestellung']))
	{
		$return = 0;		
		//hole orderposes
                $orders_productstable = $oostable['orders_products'];
		$cur_query = xtc_db_query("SELECT *
                                           FROM $orders_productstable
                                           WHERE orders_id=".intval($_POST['KeyBestellung'])."
                                           ORDER BY orders_products_id");
		while ($BestellungPos = mysql_fetch_object($cur_query))
		{
			//hole etl aufpreise
			$aufpreis=0;
                  $orders_products_attributestable = $oostable['orders_products_attributes'];
			$aufpreise_query = xtc_db_query("SELECT options_values_price 
                                                         FROM $orders_products_attributestable
                                                         WHERE orders_id=".$BestellungPos->orders_id."
                                                         AND orders_products_id=".$BestellungPos->orders_products_id." 
                                                         AND options_values_price!=0");
			while ($aufpreis_arr = mysql_fetch_row($aufpreise_query))
			{
				$aufpreis+=($aufpreis_arr[0]*(100+$BestellungPos->products_tax))/100;
			}
			//mappe bestellpos
			$kBestellPos = setMappingBestellPos($BestellungPos->orders_products_id);
			echo(CSVkonform($kBestellPos).';');
			echo(CSVkonform(intval($_POST['KeyBestellung'])).';');
			echo(CSVkonform(getEsArtikel($BestellungPos->products_id)).';');
			echo(CSVkonform($BestellungPos->products_name).';');
			echo(CSVkonform($BestellungPos->products_price-$aufpreis).';');
			echo(CSVkonform($BestellungPos->products_tax).';');
			echo(CSVkonform($BestellungPos->products_quantity).';');
			echo("\n");
		}
		//letzte Position Versand
                $orders_totaltable = $oostable['orders_total'];
		$cur_query = xtc_db_query("SELECT *
                                           FROM $orders_totaltable
                                           WHERE class=\"ot_shipping\"
                                             AND orders_id=".intval($_POST['KeyBestellung']));
		if ($Versand = mysql_fetch_object($cur_query))
		{
			//mappe bestellpos
			$kBestellPos = setMappingBestellPos(0);

			//hole versand mwst aus einstellungen 
			$cur_query = xtc_db_query("SELECT versandMwst FROM eazysales_einstellungen");
			$einstellungen = mysql_fetch_object($cur_query);
			
			echo(CSVkonform($kBestellPos).';');
			echo(CSVkonform(intval($_POST['KeyBestellung'])).';');
			echo(CSVkonform("0").';');
			echo(CSVkonform($Versand->title).';');
			echo(CSVkonform($Versand->value).';');
			echo(CSVkonform($einstellungen->versandMwst).';');
			echo(CSVkonform("1").';');
			echo("\n");
		}
	}
}


echo($return);
logge($return);
?>