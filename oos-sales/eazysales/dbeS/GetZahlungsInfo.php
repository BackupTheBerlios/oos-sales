<?php
/* ----------------------------------------------------------------------
   $Id: GetZahlungsInfo.php,v 1.8 2006/07/09 16:38:38 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetZahlungsInfo.php,v1.0  15.06.06
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
require 'syncinclude.php';
if (file_exists(DOCROOT_XTC_PATH."inc/changedataout.inc.php"))
	require_once(DOCROOT_XTC_PATH."inc/changedataout.inc.php");

$return=3;
if (auth())
{
	$return=5;
	if (intval($_POST['KeyBestellung']))
	{
		$return = 0;
		//hole order

                $orderstable = $oostable['orders'];
		$cur_query = xtc_db_query("SELECT orders_id, payment_method, cc_type, cc_owner, cc_number, cc_expires, cc_start, cc_issue, cc_cvv 
                                           FROM $orderstable
                                           WHERE orders_id=".intval($_POST['KeyBestellung']));
		$ZahlungsInfo = mysql_fetch_object($cur_query);
		$ZahlungsInfo->send=0;
		//ist es Banktransfer?
		if ($ZahlungsInfo->payment_method=="banktransfer")
		{
			//hole bankdaten
                  $banktransfertable = $oostable['banktransfer'];
			$cur_query = xtc_db_query("SELECT * FROM $banktransfertable WHERE orders_id=".intval($_POST['KeyBestellung']));
			$Bank = mysql_fetch_object($cur_query);
			if ($Bank->orders_id>0)
			{
				$ZahlungsInfo->send=1;
				$ZahlungsInfo->cBankName=$Bank->banktransfer_bankname;
				$ZahlungsInfo->cBLZ=$Bank->banktransfer_blz;
				$ZahlungsInfo->cKontoNr=$Bank->banktransfer_number;
				$ZahlungsInfo->cInhaber=$Bank->banktransfer_owner;
			}
		}
		if ($ZahlungsInfo->payment_method=="cc")
		{		
			//Kreditkarte
			//hole chainkey
			$ZahlungsInfo->send=1;

                   $configurationtable = $oostable['configuration'];
			$cur_query = xtc_db_query("SELECT $configurationtable
                                                   FROM configuration
                                                   WHERE configuration_key=\"CC_KEYCHAIN\"");
			$chain = mysql_fetch_object($cur_query);
			$ZahlungsInfo->cKartenNr = changedataout($ZahlungsInfo->cc_number,$chain->configuration_value);
			$ZahlungsInfo->cGueltigkeit = $ZahlungsInfo->cc_expires;
			$ZahlungsInfo->cCVV = $ZahlungsInfo->cc_cvv;
			$ZahlungsInfo->cKartenTyp = $ZahlungsInfo->cc_type;
			$ZahlungsInfo->cInhaber = $ZahlungsInfo->cc_owner;
		}
		
		
		if ($ZahlungsInfo->send==1)
		{
			echo(CSVkonform($ZahlungsInfo->orders_id).';');
			echo(CSVkonform($ZahlungsInfo->orders_id).';');
			echo(CSVkonform($ZahlungsInfo->cBankName).';');
			echo(CSVkonform($ZahlungsInfo->cBLZ).';');
			echo(CSVkonform($ZahlungsInfo->cKontoNr).';');
			echo(CSVkonform($ZahlungsInfo->cKartenNr).';');
			echo(CSVkonform($ZahlungsInfo->cGueltigkeit).';');
			echo(CSVkonform($ZahlungsInfo->cCVV).';');
			echo(CSVkonform($ZahlungsInfo->cKartenTyp).';');
			echo(CSVkonform($ZahlungsInfo->cInhaber).';');
			echo("\n");
		}
	}
}

echo($return);
logge($return);
?>