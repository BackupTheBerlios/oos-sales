<?php
/* ----------------------------------------------------------------------
   $Id: Artikel.php,v 1.18 2006/09/08 14:29:50 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: Artikel.php,v1.02  04.07.06
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
 * eazySales_Connector/dbeS/Artikel.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.02 / 04.07.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

  $return = 3;

  if (auth()) {

    if (intval($_POST['action']) == 1 && intval($_POST['KeyArtikel'])) {
      $return = 0;

      $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
      $query = "SELECT languages_id, tax_class_id, tax_zone_id, tax_priority
                FROM $eazysales_einstellungenstable";
      $result = $dbconn->Execute($query);
      $einstellungen = $result->fields;

      $artikel = array();
      $artikel['kArtikel'] = realEscape($_POST['KeyArtikel']);
      $artikel['cArtNr'] = realEscape($_POST['ArtikelNo']);
      $artikel['cName'] = realEscape(htmlentities($_POST['ArtikelName']));
      $artikel['cBeschreibung'] = realEscape($_POST['ArtikelBeschreibung']);
      $artikel['fVKBrutto'] = realEscape($_POST['ArtikelVKBrutto']);
      $artikel['fVKNetto'] = realEscape($_POST['ArtikelVKNetto']);
      $artikel['fMwSt'] = realEscape($_POST['ArtikelMwSt']);
      $artikel['cAnmerkung'] = realEscape($_POST['ArtikelAnmerkung']);
      $artikel['nLagerbestand'] = max(realEscape($_POST['ArtikelLagerbestand']),0);
      $artikel['cEinheit'] = realEscape($_POST['ArtikelEinheit']);
      $artikel['nMindestbestellmaenge'] = realEscape($_POST['ArtikelMindBestell']);
      $artikel['cBarcode'] = realEscape($_POST['ArtikelBarcode']);
      // $artikel['fVKHaendlerBrutto'] = realEscape($_POST['ArtikelVKHaendlerBrutto']);
      // $artikel['fVKHaendlerNetto'] = realEscape($_POST['ArtikelVKHaendlerNetto']);
      $artikel['cTopArtikel'] = realEscape($_POST['TopAngebot']);
      $artikel['fGewicht'] = realEscape($_POST['Gewicht']);
      $artikel['cNeu'] = realEscape($_POST['Neu']);
      // $artikel['cKurzBeschreibung'] = realEscape($_POST['ArtikelKurzBeschreibung']);
      $artikel['fUVP'] = realEscape($_POST['ArtikelUVP']);
      $artikel['cHersteller'] = realEscape(htmlentities($_POST['Hersteller']));
/*
      $startseite = 0;
      if ($artikel['cTopArtikel']=="Y") {
        $startseite = 1;
      }
*/

/*
      $shipping_status=0;
      if ($GLOBALS['einstellungen']->shipping_status_id>0) {
         $shipping_status=$GLOBALS['einstellungen']->shipping_status_id;
      }
*/

      //update oder insert?
      $products_id = getFremdArtikel($artikel['kArtikel']);
      if ($products_id > 0){
        //update
        $products_attributestable = $oostable['products_attributes'];
        $dbconn->Execute("DELETE FROM $products_attributestable WHERE products_id = '" . $products_id . "'");

        $products_to_categoriestable = $oostable['products_to_categories'];
        $dbconn->Execute("DELETE FROM $products_to_categoriestable WHERE products_id = '" . $products_id . "'");

        //evtl. andere MwSt?
        $products_tax_class_id = holeSteuerId($artikel['fMwSt'], $einstellungen['tax_zone_id'], $einstellungen['tax_priority']);
        //evtl. neuer Hersteller?
        $manufacturers_id = holeHerstellerId($artikel['cHersteller'], $einstellungen['languages_id']);

        $productstable = $oostable['products'];
        $dbconn->Execute("UPDATE $productstable
                          SET products_model = '" . $artikel['cArtNr'] . "',
                              products_price = '" . $artikel['fVKNetto'] . "',
                              products_tax_class_id = '" . $products_tax_class_id . "',
                              products_quantity = '" . $artikel['nLagerbestand'] . "',
                              products_ean = '" . $artikel['cBarcode'] . "',
                              products_weight = '" . $artikel['fGewicht'] . "',
                              manufacturers_id = '" . $manufacturers_id . "',
                              products_status = '1'
                          WHERE products_id = = '" . $products_id . "'");

        $products_descriptiontable = $oostable['products_description'];
        $dbconn->Execute("UPDATE $products_descriptiontable
                          SET products_name = '" . $artikel['cName'] . "',
                              products_description = '" . $artikel['cBeschreibung'] . "',
                              products_keywords = '',
                              products_meta_title = '' ,
                              products_meta_description = '',
                              products_meta_keywords = '',
                              products_url = ''
                          WHERE products_id = '" . $products_id . "' AND
                                language_id = '" . $einstellungen['languages_id'] . "'");

      } else {
        //insert
        //hole Mwst classId
        $products_tax_class_id = holeSteuerId($artikel['fMwSt'], $einstellungen['tax_zone_id'], $einstellungen['tax_priority']);
        //setze Hersteller, falls es ihn noch nicht gibt
        $manufacturers_id = holeHerstellerId($artikel['cHersteller'], $einstellungen['languages_id']);

        $productstable = $oostable['products'];
        $dbconn->Execute("INSERT INTO $productstable 
                         (products_model,
                          products_price,
                          products_tax_class_id,
                          products_quantity,
                          products_ean,
                          products_weight,
                          manufacturers_id,
                          products_status) VALUES ('" . $artikel['cArtNr'] . "',
                                                   '" . $artikel['fVKNetto'] . "',
                                                   '" . $products_tax_class_id . "',
                                                   '" . $artikel['nLagerbestand'] . "',
                                                   '" . $artikel['cBarcode'] . "',
                                                   '" . $artikel['fGewicht'] . "',
                                                   '" . $manufacturers_id . "',
                                                    1)");
        //hole id
        $products_id = $dbconn->Insert_ID();

        $products_descriptiontable = $oostable['products_description'];
        $dbconn->Execute("INSERT INTO $products_descriptiontable
                          (products_id,
                           products_name,
                           products_description,
                           language_id) VALUES ('" . $products_id . "',
                                                '" . $artikel['cName'] . "',
                                                '" . $artikel['cBeschreibung'] . "',
                                                '" . $einstellungen['languages_id'] . "'");
        setMappingArtikel($artikel['kArtikel'],$products_id);

      }
      //VPE
      if ($products_id>0) {
        $products_units_id = 0;

        $products_unitstable = $oostable['products_units'];
        $query = "SELECT products_units_id
                  FROM $products_unitstable
                  WHERE language_id = '" . $einstellungen['languages_id'] . "' AND
                        products_unit_name = '" . $artikel['cEinheit']. "'");
        $result =& $dbconn->Execute($query);
        if ($result->RecordCount()) {
          $products_units_id = $result->->fields['products_units_id'];
        } else {
          $products_unitstable = $oostable['products_units'];
          $next_id_result = $dbconn->Execute("SELECT max(products_units_id) as products_units_id FROM $products_unitstable");
          $next_id = $next_id_result->fields;
          $products_units_id = $next_id['products_units_id'] + 1;
          $dbconn->Execute("INSERT INTO $products_unitstable 
                            (products_units_id,
                             language_id,
                             products_unit_name) VALUES ('" . $products_units_id . "',
                                                         '" . $einstellungen['languages_id'] . "',
                                                         '" . $artikel['cEinheit'] . "'");
        }
        $productstable = $oostable['products'];
        $dbconn->Execute("UPDATE $productstable 
                          SET products_units_id = '" . $products_units_id . "',
                          WHERE products_id = '" . $products_id . "'");
      }
    } else {
      $return = 5;
    }

    if (intval($_POST['action']) == 3 && intval($_POST['KeyArtikel'])) {
      $products_id = getFremdArtikel(intval($_POST['KeyArtikel']));
      if ($products_id > 0) {
        $productstable = $oostable['products'];
        $dbconn->Execute("UPDATE $productstable SET products_status = 0 WHERE products_id = '" . $products_id . "'");
        $return = 0;
      }
    }
  }

  echo($return);
  logge($return);


  function holeHerstellerId($cHersteller, $lang_id) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    if (strlen($cHersteller) >0 ) {
      $manufacturerstable = $oostable['manufacturers'];
      $query = "SELECT manufacturers_id
                FROM $manufacturerstable
                WHERE manufacturers_name = '" . $cHersteller . "'";
      $result =& $dbconn->Execute($query);
      if ($result->RecordCount()) {
        return $result->fields['manufacturers_id'];
      } else {
        $manufacturerstable = $oostable['manufacturers'];
        $dbconn->Execute("INSERT INTO $manufacturerstable
                          (manufacturers_name,
                           date_added) VALUES ('" . $cHersteller . "',
                                               now())");

        $id = $dbconn->Insert_ID();
        $manufacturers_infotable = $oostable['manufacturers_info'];
        $dbconn->Execute("INSERT INTO $manufacturers_infotable 
                          (manufacturers_id,
                           manufacturers_languages_id) VALUES ('" . $id . "',
                                                               '" . $lang_id . "'");
         return $id;
      }
    }
    return 0;
  }


  function holeSteuerId($MwSt, $tax_zone_id, $tax_priority) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $tax_ratestable = $oostable['tax_rates'];
    $query = "SELECT tax_class_id
              FROM $tax_ratestable
              WHERE tax_zone_id = '" . $tax_zone_id . "' AND 
                    tax_rate = '" . $MwSt . "'");
    $result =& $dbconn->Execute($query);
    if ($result->RecordCount()) {
      return $result->tax_class_id;
    } else {
      $tax_classtable = $oostable['tax_class'];
      $dbconn->Execute("INSERT INTO $tax_classtable 
                       (tax_class_title,
                        date_added) VALUES (eazySales Steuerklasse ".$MwSt."%, now())");
      $tax_class_id = $dbconn->Insert_ID();

      $tax_ratestable = $oostable['tax_rates'];
      $dbconn->Execute("INSERT INTO $tax_ratestable 
                       (tax_zone_id,
                        tax_class_id,
                        tax_priority,
                        tax_rate,
                        date_added) VALUES ('" . $tax_zone_id . "',
                                            '" . $tax_class_id . "',
                                            '" . $tax_priority . "', 
                                            '" . $MwSt . "', 
                                            now())");
		return $tax_class_id_arr[0];
	}
}
?>