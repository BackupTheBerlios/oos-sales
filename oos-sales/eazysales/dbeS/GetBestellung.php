<?php
/* ----------------------------------------------------------------------
   $Id: GetBestellung.php,v 1.7 2006/07/09 15:44:46 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetBestellung.php,v1.01  04.07.06
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
 * eazySales_Connector/dbeS/GetBestellung.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.01 / 04.07.06
*/

require 'syncinclude.php';

$return=3;
if (auth())
{
	$return=0;	
	//hole alle neuen order	
        $orderstable = $oostable['orders'];
	$cur_query = xtc_db_query("SELECT orders.payment_method, orders.orders_id, orders.customers_id, orders.comments, date_format(orders.date_purchased, \"%d.%m.%Y\") as ErstelltDatumF 
                                   FROM $orderstable LEFT JOIN
                                        eazysales_sentorders ON orders.orders_id=eazysales_sentorders.orders_id
                                   WHERE eazysales_sentorders.orders_id is NULL limit 1");
	if ($Bestellung = mysql_fetch_object($cur_query))
	{
		//falls kein kunde existiert, key muss irgendwo her!
		if (!$Bestellung->customers_id)
			$Bestellung->customers_id = 10000000-$Bestellung->orders_id;
		
		$VersandKey = 0;
		//tu Zahlungsweise in Comment:
		switch ($Bestellung->payment_method)
		{
			case 'banktransfer':
				$Bestellung->zahlungsweise = "Zahlungsweise: Lastschrift";
				$VersandKey = -1;
				break;
			case 'cc':
				$Bestellung->zahlungsweise = "Zahlungsweise: Kreditkarte";
				$VersandKey = -1;
				break;
			case 'cod':
				$Bestellung->zahlungsweise = "Zahlungsweise: Nachnahme";
				break;
			case 'invoice':
				$Bestellung->zahlungsweise = "Zahlungsweise: Auf Rechnung";
				break;
			default:
				$Bestellung->zahlungsweise = "Zahlungsweise: $Bestellung->payment_method";
				break;
		}		
		echo(CSVkonform($Bestellung->orders_id).';');
		echo(CSVkonform($Bestellung->orders_id).';');
		echo(CSVkonform($Bestellung->customers_id).';');
		echo(CSVkonform($Bestellung->orders_id).';');
		echo(CSVkonform($VersandKey).';');
		echo(';'); //VersandInfo
		echo(';'); //Versanddatum
		echo(';'); //Tracking Nr
		echo(CSVkonform($Bestellung->zahlungsweise).';');
		echo(';'); //Abgeholt
		echo(';'); //Status
		echo(CSVkonform($Bestellung->ErstelltDatumF).';'); 
		echo(CSVkonform($Bestellung->orders_id).';');
		echo(CSVkonform($Bestellung->comments).';');
		echo("\n");
	}
}

logge($return);
?>