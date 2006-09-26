<?php
/* ----------------------------------------------------------------------
   $Id: index.php,v 1.9 2006/09/26 00:12:49 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: index.php,v 1.0 14.06.06
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
 * eazySales_Connector/index.php
 * AdminLogin fr eazySales Connector
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 14.06.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'admininclude.php';
  require 'adminTemplates.php';

  //adminlogin
  if (intval($_POST["adminlogin"])==1) {
    $user_query = xtc_db_query("SELECT * FROM customers WHERE customers_email_address=\"".realEscape($_POST["benutzer"])."\" AND customers_password=\"".md5($_POST["passwort"])."\"");


    $user_query = "SELECT admin_id, admin_groups_id, admin_firstname, admin_email_address, admin_password, 
                          admin_modified, admin_logdate, admin_lognum
                   FROM " . $oostable['admin'] . "
                   WHERE admin_email_address = '" . oos_db_input($_POST['benutzer']) . "'");
    $check_admin_result = $dbconn->Execute(

    if (!$check_admin_result->RecordCount()) {
      $_SESSION['loggedIn'] = 'fail';
    } else {
      $check_admin = $check_admin_result->fields;
      // Check that password is good
      if (!oos_validate_password($_POST['passwort'], $check_admin['login_password'])) {
        $_SESSION['loggedIn'] = 'fail';
      } else {
        $_SESSION['loggedIn'] = '1';
      }
    }

    zeigeKopf();
    zeigeLinks($_SESSION["loggedIn"]);

    if ($_SESSION["loggedIn"] != '1') {
      zeigeLogin();
    } else {
      zeigeLoginBereich();
    }

    zeigeFuss();
  }

?>