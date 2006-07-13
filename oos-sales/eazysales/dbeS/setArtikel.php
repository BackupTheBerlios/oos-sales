<?php
/* ----------------------------------------------------------------------
   $Id: setArtikel.php,v 1.7 2006/07/13 03:41:08 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: setArtikel.php,v 1.0 15.06.06
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

  //Auth
  if (auth()) {
    $return = 0;

    //data da?
    if ($_POST['data']) {
      $zeilen = explode("\n",$_POST['data']);

      if (is_array($zeilen)) {
        foreach ($zeilen as $zeile) {
          $werte = explode(";",$zeile);

          switch ($werte[0]) {
             case 'P':
                setMappingArtikel($werte[1],$werte[2]);
                break;

             case 'K':
                setMappingKategorie($werte[1],$werte[2]);
                break;

             case 'W':
                setMappingEigenschaftsWert($werte[1],$werte[2],$werte[3]);
                break;
           }

        }
      }
    }
  }

  echo($return);
  logge($return);
?>