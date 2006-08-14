<?php
/* ----------------------------------------------------------------------
   $Id: SetBestellung.php,v 1.16 2006/08/14 23:45:55 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: SetBestellung.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/SetBestellung.php
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
  if (auth()) {
    $return = 5;

    //Bestellung versandt
    if (intval($_POST["action"]) == 6 && intval($_POST['KeyBestellung'])) {
      $return = 0;

       //hole einstellungen
      $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
      $query = "SELECT StatusVersendet FROM $eazysales_einstellungenstable");
      $status_versendet = $dbconn->GetOne($query);

      //setze status der Bestellung
      if ($status_versendet>0) {
        $orderstable = $oostable['orders'];
        $dbconn->Execute("UPDATE $orderstable SET orders_status=".$status_versendet." WHERE orders_id=".intval($_POST['KeyBestellung']));

			$VersandInfo = realEscape($_POST["VersandInfo"]);
			$VersandDatum = realEscape($_POST["VersandDatum"]);
			$Tracking = realEscape($_POST["Tracking"]);
			$kommentar = "Bestellung aus eazySales am $VersandDatum versandt.\n".$VersandInfo."\nIdentCode".$Tracking;

        $orders_status_historytable = $oostable['orders_status_history'];
        $dbconn->Execute("INSERT INTO $orders_status_historytable 
                         (orders_id,
                          orders_status_id,
                          date_added,
                          comments) VALUES(".intval($_POST['KeyBestellung']).",
                                           ".$status_versendet.",
                                            now(),
                                           \"".$kommentar."\")");
      }
    }

    //Bestellung erfolgreich abgeholt
    if (intval($_POST["action"]) == 5 && intval($_POST['KeyBestellung'])) {
      $return = 0;

      //hole einstellungen
      $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
      $query = "SELECT StatusAbgeholt FROM $eazysales_einstellungenstable");
      $status_abgeholt = $dbconn->GetOne($query);

      //setze status der Bestellung
      if ($status_abgeholt > 0) {

        $orderstable = $oostable['orders'];
        $dbconn->Execute("UPDATE $orderstable SET orders_status=".$status_abgeholt." WHERE orders_id=".intval($_POST['KeyBestellung']));
        //fuege history hinzu
        $kommentar = "Erfolgreich in eazySales bernommen";

        $orders_status_historytable = $oostable['orders_status_history'];
        $dbconn->Executey("INSERT INTO $orders_status_historytable 
                           (orders_id,
                            orders_status_id,
                            date_added,
                            comments) VALUES(".intval($_POST['KeyBestellung']).",
                                              ".$einstellungen->StatusAbgeholt.",
                                               now(),
                                                \"".$kommentar."\")");
      }

      //setze bestellung auf abgeholt
      $eazysales_sentorderstable = $oostable['eazysales_sentorders'];
      $dbconn->Executey("INSERT INTO $eazysales_sentorderstable 
                        (orders_id,
                         dGesendet) VALUES (".intval($_POST['KeyBestellung']).",
                                              now())");
    }
  }

  echo($return);
  logge($return);
?>