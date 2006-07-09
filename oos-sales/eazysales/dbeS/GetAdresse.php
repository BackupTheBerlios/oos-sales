<?php
/* ----------------------------------------------------------------------
   $Id: GetAdresse.php,v 1.4 2006/07/09 02:07:11 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetAdresse.php,v1.02  05.07.06
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
 * eazySales_Connector/dbeS/GetAdresse.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.02 / 05.07.06
*/
require 'syncinclude.php';

$return=3;
if (auth())
{
	if (intval($_POST['KeyAdresse']))
	{
		//hole order
		$cur_query = eS_execute_query("SELECT * FROM orders WHERE orders_id=".intval($_POST['KeyAdresse']));
		$Order = mysql_fetch_object($cur_query);
		if (!$Order->delivery_firstname && !$Order->delivery_lastname)
		{
			list($Order->delivery_firstname, $Order->delivery_lastname) = split(" ",$Order->delivery_name);
		}
		
		//falls kein kunde existiert, key muss irgendwo her!
		if (!$Order->customers_id)
			$Order->customers_id = 10000000-$Order->orders_id;

		
		echo(CSVkonform($Order->orders_id).';');
		echo(CSVkonform($Order->customers_id).';');
		echo(CSVkonform($Order->delivery_firstname).';');
		echo(CSVkonform($Order->delivery_lastname).';');
		echo(CSVkonform($Order->delivery_company).';');
		echo(CSVkonform($Order->delivery_street_address).';');
		echo(CSVkonform($Order->delivery_postcode).';');
		echo(CSVkonform($Order->delivery_city).';');
		echo(CSVkonform($Order->delivery_country).';');
		echo(CSVkonform($Order->customers_telephone).';');
		echo(';'); //keine Faxangaben
		echo(CSVkonform($Order->customers_email_address).';');
		echo("\n");
		
		$return=0;
 	}
	else
		$return=5;
}

mysql_close();
echo($return);
logge($return);
?>