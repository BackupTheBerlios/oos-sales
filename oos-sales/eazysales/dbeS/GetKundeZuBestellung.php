<?php
/* ----------------------------------------------------------------------
   $Id: GetKundeZuBestellung.php,v 1.7 2006/07/09 15:35:14 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetKundeZuBestellung.php,v1.02  04.07.06
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
 * eazySales_Connector/dbeS/GetKundeZuBestellung.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.02 / 04.07.06
*/

require 'syncinclude.php';

$return=3;
if (auth())
{
	if (intval($_POST['KeyBestellung']))
	{
		$return=0;
		
		//hole einstellungen 
		$cur_query = xtc_db_query("SELECT mappingHaendlerkunde FROM eazysales_einstellungen");
		$einstellungen = mysql_fetch_object($cur_query);
		$haendler_arr = explode(";",$einstellungen->mappingHaendlerkunde);
		
		//hole order
                $orderstable = $oostable['orders'];
		$cur_query = xtc_db_query("SELECT *
                                           FROM $orderstable
                                           WHERE orders_id=".intval($_POST['KeyBestellung']));
		$Kunde = mysql_fetch_object($cur_query);

		//zusatzinfos vom kunden holen
                $orderstable = $oostable['orders'];
                $customerstable = $oostable['customers'];
		$cur_query = xtc_db_query("SELECT customers.customers_gender, customers.customers_newsletter, customers.customers_fax, customers.customers_vat_id 
                                           FROM $orderstable,
                                                $customerstable
                                           WHERE orders.customers_id=customers.customers_id 
                                             AND customers.customers_id=".$Kunde->customers_id);
		$cust = mysql_fetch_object($cur_query);
		
		$Kunde->customers_gender = $cust->customers_gender;
		$Kunde->customers_newsletter = $cust->customers_newsletter;
		$Kunde->customers_fax = $cust->customers_fax;
		$Kunde->customers_vat_id = $cust->customers_vat_id;
		
		$Kunde->cAnrede="F";
		if ($Kunde->customers_gender=="m")
			$Kunde->cAnrede="H";
			
		$Kunde->cHaendler="N";
		if (in_array($Kunde->customers_status,$haendler_arr))
			$Kunde->cHaendler="Y";
			
		$Kunde->cNewsletter="N";
		if ($Kunde->customers_newsletter)
			$Kunde->cNewsletter="Y";
			
		if (!$Kunde->billing_firstname && !$Kunde->billing_lastname)
		{
			list($Kunde->billing_firstname, $Kunde->billing_lastname) = split(" ",$Kunde->billing_name);
		}
		
		//falls kein kunde existiert, key muss irgendwo her!
		if (!$Kunde->customers_id)
			$Kunde->customers_id = 10000000-$Kunde->orders_id;
		
		echo(CSVkonform($Kunde->customers_id).';');
		echo(CSVkonform($Kunde->customers_id).';');
		echo(';');
		echo('"*****";');
		echo(CSVkonform($Kunde->cAnrede).';');
		echo(';'); //Titel
		echo(CSVkonform($Kunde->billing_firstname).';');
		echo(CSVkonform($Kunde->billing_lastname).';');
		echo(CSVkonform($Kunde->billing_company).';');
		echo(CSVkonform($Kunde->billing_street_address).';');
		echo(CSVkonform($Kunde->billing_postcode).';');
		echo(CSVkonform($Kunde->billing_city).';');
		echo(CSVkonform($Kunde->billing_country).';');
		echo(CSVkonform($Kunde->customers_telephone).';');
		echo(CSVkonform($Kunde->customers_fax).';');
		echo(CSVkonform($Kunde->customers_email_address).';');
		echo(CSVkonform($Kunde->cHaendler).';');
		echo(';'); //Rabatt
		echo(CSVkonform($Kunde->customers_vat_id).';');
		echo(CSVkonform($Kunde->cNewsletter).';');
		echo("\n");
 	}
	else
		$return=5;
}

echo($return);
logge($return);
?>