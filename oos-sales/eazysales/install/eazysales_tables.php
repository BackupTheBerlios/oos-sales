<?php
/* ----------------------------------------------------------------------
   $Id: eazysales_tables.php,v 1.1 2006/07/09 22:52:25 r23 Exp $

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


  function idxsql($idxname, $table, $idxflds) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $dict = NewDataDictionary($dbconn);

    $sqlarray = $dict->CreateIndexSQL($idxname, $table, $idxflds);
    $dict->ExecuteSQLArray($sqlarray);
  }

?>