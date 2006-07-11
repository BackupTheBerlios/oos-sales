<?php
/* ----------------------------------------------------------------------
   $Id: eazysales_tables.php,v 1.2 2006/07/11 15:58:56 r23 Exp $

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
    nSessionExpires I DEFAULT '0' NULL,
    cSessionData X
  ";
  dosql($table, $flds);


?>