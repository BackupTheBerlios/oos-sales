<?php
/* ----------------------------------------------------------------------
   $Id: Kategorie.php,v 1.16 2006/09/26 00:51:20 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: Kategorie.php,v1.01  05.07.06
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
 * eazySales_Connector/dbeS/Kategorie.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.01 / 05.07.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

  $return = 3;

  if (auth()) {
    //hole einstellungen
    $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
    $query = "SELECT currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL,
                     tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,
                     cat_listing_template, cat_category_template, cat_sorting, cat_sorting2,
                     prod_product_template, prod_options_template, StatusAbgeholt, StatusVersendet
              FROM $eazysales_einstellungenstable";
    $einstellungen = $dbconn->Execute($query);

    if ((intval($_POST['action']) == 1 || intval($_POST['action']) == 3) && intval($_POST['KeyKategorie'])>0) {
      $return = 0;
      $Kategorie->kKategorie = intval($_POST['KeyKategorie']);
      $Kategorie->kOberKategorie = intval($_POST["KeyOberKategorie"]);
      $Kategorie->cName = realEscape($_POST["KeyName"]);
      $Kategorie->cBeschreibung = realEscape(htmlentities($_POST["KeyBeschreibung"]));
      $Kategorie->parent_id = 0;

      if ($Kategorie->kOberKategorie>0) {
        //existiert oberkat?
        $categories_id_oberkat = getFremdKategorie($Kategorie->kOberKategorie);
        if (!$categories_id_oberkat) {
          $categoriestable = $oostable['categories'];
          $dbconn->Execute("INSERT INTO $categoriestable 
                           (categories_status,
                            date_added,
                            products_sorting) VALUES (0,
                                                      now(),
                                                      $einstellungen->cat_sorting)");
          $insert_id = $dbconn->Insert_ID();

          $categories_descriptiontable = $oostable['categories_description'];
          $dbconn->Execute("INSERT INTO $categories_descriptiontable 
                           (categories_id,
                            language_id) VALUES (".$insert_id",$einstellungen->languages_id)");
          $Kategorie->parent_id = $categories_id_oberkat_arr[0];
          setMappingKategorie($Kategorie->kOberKategorie, $Kategorie->parent_id);
        } else {
           $Kategorie->parent_id = $categories_id_oberkat;
        }

        //update oder insert?
        $categories_id = getFremdKategorie($_POST['KeyKategorie']);
         if ($categories_id>0) {
           //update
           $categoriestable = $oostable['categories'];
           $dbconn->Execute("UPDATE $categoriestable SET parent_id=$Kategorie->parent_id, categories_status=1 WHERE categories_id=".$categories_id);

           $categories_descriptiontable = $oostable['categories_description'];
           $dbconn->Execute("UPDATE $categories_descriptiontable SET categories_name=\"$Kategorie->cName\", categories_description=\"$Kategorie->cBeschreibung\" WHERE categories_id=".$categories_id." AND language_id=".$einstellungen->languages_id);
         } else {
           //insert
           $categoriestable = $oostable['categories'];
           $dbconn->Execute("INSERT INTO $categoriestable 
                             (parent_id,
                              categories_status,
                              products_sorting,
                             date_added) VALUES ($Kategorie->parent_id,
                                                  1,\"".
                                                 $einstellungen->cat_sorting."\",
                                                 now())");
           $insert_id = $dbconn->Insert_ID();

           $categories_descriptiontable = $oostable['categories_description'];
           $dbconn->Execute("INSERT INTO $categories_descriptiontable 
                             (categories_id,
                              language_id,
                              categories_name,
                              categories_description) VALUES (".$insert_id.",
                                                                $einstellungen->languages_id, \"
                                                                $Kategorie->cName\", \"
                                                                $Kategorie->cBeschreibung\")");
           setMappingKategorie($Kategorie->kKategorie, $categories_id_arr[0]);
         }
       }

       if (intval($_POST['action']) == 3 && intval($_POST['KeyKategorie'])>0) {
         $return = 0;
         $cat = getFremdKategorie(intval($_POST['KeyKategorie']));
         if ($cat>0) {
           $categoriestable = $oostable['categories'];
           $dbconn->Execute("UPDATE $categoriestable SET categories_status=0 WHERE categories_id=".$cat);
       }
    }
  }

  echo($return);
  logge($return);

?>