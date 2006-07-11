<?php
/* ----------------------------------------------------------------------
   $Id: eazysales_tables.php,v 1.3 2006/07/11 16:13:16 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: eazySales_connector_DB.sql,v 1.00 14.06.06
   ----------------------------------------------------------------------

   eazySales Connector
   http://www.jtl-software.de/eazysales.php

   Copyright (c) 2006, JTL-Software
   ----------------------------------------------------------------------
   Original Author of file:  JTL-Software <thomas@jtl-software.de>
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  if (!function_exists('dosql')) {
    function dosql($table, $flds) {

      // Get database information
      $dbconn =& oosDBGetConn();
      $oostable =& oosDBGetTables();

      $dict = NewDataDictionary($dbconn);

      $taboptarray = array('mysql' => 'TYPE=MyISAM', 'REPLACE');

      $sqlarray = $dict->CreateTableSQL($table, $flds, $taboptarray);
      $dict->ExecuteSQLArray($sqlarray); 

      echo '<br /><img src="images/yes.gif" alt="" border="0" align="absmiddle"> <font class="oos-title">' . $table .  'erstellt.</font>';
    }
  }


  if (!function_exists('idxsql')) {
    function idxsql($idxname, $table, $idxflds) {

      // Get database information
      $dbconn =& oosDBGetConn();
      $oostable =& oosDBGetTables();

      $dict = NewDataDictionary($dbconn);

      $sqlarray = $dict->CreateIndexSQL($idxname, $table, $idxflds);
      $dict->ExecuteSQLArray($sqlarray);
    }
  }


  $table = $prefix_table . 'eazysales_adminsession';
  $flds = "
    cSessionId C(255) DEFAULT NULL,
    nSessionExpires I UNSIGNED DEFAULT '0' NULL,
    cSessionData X
  ";
  dosql($table, $flds);


  $table = $prefix_table . 'eazysales_einstellungen';
  $flds = "
    currencies_id I2 DEFAULT NULL,
    languages_id I2 DEFAULT NULL,
    mappingEndkunde C(255) DEFAULT NULL,
    mappingHaendlerkunde C(255) DEFAULT NULL,
    shopURL C(255) DEFAULT NULL,
    tax_class_id I DEFAULT NULL,
    tax_zone_id I DEFAULT NULL,
    tax_priority I DEFAULT NULL,
    shipping_status_id I DEFAULT NULL,
    versandMwst N '15.4' NOTNULL DEFAULT '0.0000',
    cat_listing_template C(255) DEFAULT NULL,
    cat_category_template C(255) DEFAULT NULL,
    cat_sorting C(255) DEFAULT NULL,
    cat_sorting2 C(255) DEFAULT NULL,
    prod_product_template C(255) DEFAULT NULL,
    prod_options_template C(255) DEFAULT NULL,
    StatusAbgeholt I1 UNSIGNED NOT NULL DEFAULT '0',
    StatusVersendet I1 UNSIGNED NOT NULL DEFAULT '0'
  ";
  dosql($table, $flds);

?>