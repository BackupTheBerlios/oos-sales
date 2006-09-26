<?php
/* ----------------------------------------------------------------------
   $Id: GetZahlungsInfo.php,v 1.11 2006/09/26 00:51:20 r23 Exp $

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

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

  $return = 3;

  if (auth()) {

    $return = 5;
    if (intval($_POST['KeyBestellung'])) {
      $return = 0;
      //hole order

      $orderstable = $oostable['orders'];
      $query = "SELECT orders_id, payment_method, cc_type, cc_owner, cc_number, cc_expires,
                FROM $orderstable
                WHERE orders_id = " . intval($_POST['KeyBestellung'] ));
      $result =& $dbconn->Execute($query);
      $ZahlungsInfo = $result->fields;


		//ist es Banktransfer?
		if ($ZahlungsInfo->payment_method=="banktransfer")
		{
			//hole bankdaten
    $banktransfertable = $oostable['banktransfer'];
    $banktransfer_result = $dbconn->Execute("SELECT banktransfer_prz, banktransfer_status, banktransfer_owner, banktransfer_number, banktransfer_bankname, banktransfer_blz, banktransfer_fax FROM $banktransfertable  WHERE orders_id = '" . oos_db_input($_GET['oID']) . "'");
    $banktransfer = $banktransfer_result->fields;

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