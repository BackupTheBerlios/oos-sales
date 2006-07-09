<?php
/* ----------------------------------------------------------------------
   $Id: admininclude.php,v 1.5 2006/07/09 02:39:06 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: admininclude.php,v 1.0 16.06.06
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

require '../paths.php';
require_once("AdminSession.php");

//get DB Connecion
// Set the local configuration parameters - mainly for developers
if (file_exists(DOCROOT_XTC_PATH.'includes/local/configure.php')) 
	include(DOCROOT_XTC_PATH.'includes/local/configure.php');
// include server parameters
require_once (DOCROOT_XTC_PATH.'includes/configure.php');


// require  the database functions
  $adodb_logsqltable = $oostable['adodb_logsql'];
  if (!defined('ADODB_LOGSQL_TABLE')) {
    define('ADODB_LOGSQL_TABLE', $adodb_logsqltable);
  }
  require  OOS_ADODB . 'adodb-errorhandler.inc.php';
  require  OOS_ADODB . 'adodb.inc.php';
  require  OOS_FUNCTIONS . 'function_db.php';

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
function realEscape ($ausdruck)
{
	if (get_magic_quotes_gpc())
		return mysql_real_escape_string(stripslashes($ausdruck));
	else
		return mysql_real_escape_string($ausdruck);
}