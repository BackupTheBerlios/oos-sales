<?php
/* ----------------------------------------------------------------------
   $Id: admininclude.php,v 1.8 2006/07/11 07:34:15 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: admininclude.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/admininclude.php
 * 
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  if(!defined('SHOP_ROOT')) {
    define('SHOP_ROOT', dirname(__FILE__) . '/../../../');
  }

  require SHOP_ROOT. 'includes/config.php';

  require SHOP_ROOT . OOS_INCLUDES . 'oos_tables.php';
  require SHOP_ROOT . OOS_FUNCTIONS . 'function_kernel.php';

// define how the session functions will be used 
  require SHOP_ROOT . OOS_FUNCTIONS . 'function_session.php';

// set the session ID if it exists
  if (isset($_POST[oos_session_name()])) {
    oos_session_id($_POST[oos_session_name()]);
  } elseif (isset($_GET[oos_session_name()])) {
    oos_session_id($_GET[oos_session_name()]);
  }

  oos_session_name('eSConnectorAdm');
  oos_session_start();

  if (!isset($_SESSION)) {
    $_SESSION = array();
  }

// require  the database functions
  $adodb_logsqltable = $oostable['adodb_logsql'];
  if (!defined('ADODB_LOGSQL_TABLE')) {
    define('ADODB_LOGSQL_TABLE', $adodb_logsqltable);
  }
  require SHOP_ROOT . OOS_ADODB . 'adodb-errorhandler.inc.php';
  require SHOP_ROOT . OOS_ADODB . 'adodb.inc.php';
  require SHOP_ROOT . OOS_FUNCTIONS . 'function_db.php';

// make a connection to the database... now
  if (!oosDBInit()) {
    die('Unable to connect to database server!');
  }

  $dbconn =& oosDBGetConn();
  oosDB_importTables($oostable);



/**
 * real mysql escape mysql escape
 * @access public
 * @param string $ausdruck Ausdruck, der escaped fr mysql werden soll
 * @return escaped expression
 */
  function realEscape ($ausdruck) {
    if (get_magic_quotes_gpc()) {
      return mysql_real_escape_string(stripslashes($ausdruck));
    } else {
      return mysql_real_escape_string($ausdruck);
    }
  }
?>