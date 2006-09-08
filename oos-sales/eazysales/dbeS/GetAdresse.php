<?php
/* ----------------------------------------------------------------------
   $Id: GetAdresse.php,v 1.11 2006/09/08 14:54:51 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: GetAdresse.php,v1.02  05.07.06
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

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

  $return = 3;

  if (auth()) {

    if (intval($_POST['KeyAdresse'])) {
      //hole order
      $orderstable = $oostable['orders'];
      $query = "SELECT orders_id, customers_id, customers_telephone, customers_email_address,
                       delivery_name, delivery_company, delivery_street_address, delivery_city,
                       delivery_postcode, delivery_country
                FROM $orderstable
                WHERE orders_id= '" . intval($_POST['KeyAdresse']) . "'";
      $result =& $dbconn->Execute($query);
      $order = $result->fields;

      if (!$order['delivery_firstname'] && !$order['delivery_lastname']) {
        list($order['delivery_firstname'], $order['delivery_lastname']) = split(" ",$Order->delivery_name);
      }

      //falls kein kunde existiert, key muss irgendwo her!
      if (!$order['customers_id']) {
        $order['customers_id'] = 10000000-$order['orders_id'];
      }

      echo(CSVkonform($order['orders_id']).';');
      echo(CSVkonform($order['customers_id']).';');
      echo(CSVkonform($order['delivery_firstname']).';');
      echo(CSVkonform($order['delivery_lastname']).';');
      echo(CSVkonform($order['delivery_company']).';');
      echo(CSVkonform($order['delivery_street_address']).';');
      echo(CSVkonform($order['delivery_postcode']).';');
      echo(CSVkonform($order['delivery_city']).';');
      echo(CSVkonform($order['delivery_country']).';');
      echo(CSVkonform($order['customers_telephone']).';');
      echo(';'); //keine Faxangaben
      echo(CSVkonform($order['customers_email_address']).';');
      echo("\n");

      $return = 0;
    } else {
      $return = 5;
    }
  }

  echo($return);
  logge($return);
?>