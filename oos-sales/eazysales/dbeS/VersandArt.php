<?php
/* ----------------------------------------------------------------------
   $Id: VersandArt.php,v 1.9 2006/09/26 00:51:20 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: VersandArt.php,v 1.0 16.06.06
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
 * eazySales_Connector/dbeS/VersandArt.php
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
    if (intval($_POST['action']) == 1 && intval($_POST['KeyVersandArt'])) {
      $return = 0;
    } else {
      $return = 5;

     if (intval($_POST['action']) == 3 && intval($_POST['KeyVersandArt'])) {
       $return = 0;
     }
  }

  echo($return);
  logge($return);
?>