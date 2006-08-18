<?php
/* ----------------------------------------------------------------------
   $Id: Artikel.php,v 1.17 2006/08/18 23:12:51 r23 Exp $

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

    if (intval($_POST["action"]) == 1 && intval($_POST['KeyArtikel'])) {
      $return = 0;
      //hole einstellungen
      $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
      $query = "SELECT currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL,
                       tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,
                       cat_listing_template, cat_category_template, cat_sorting, cat_sorting2,
                       prod_product_template, prod_options_template, StatusAbgeholt, StatusVersendet
                FROM $eazysales_einstellungenstable";
      $einstellungen = $dbconn->Execute($query);


		$artikel->kArtikel = realEscape($_POST["KeyArtikel"]);
		$artikel->cArtNr = realEscape($_POST["ArtikelNo"]);
		$artikel->cName = realEscape(htmlentities($_POST["ArtikelName"]));
		$artikel->cBeschreibung = realEscape($_POST["ArtikelBeschreibung"]);
		$artikel->fVKBrutto = realEscape($_POST["ArtikelVKBrutto"]);
		$artikel->fVKNetto = realEscape($_POST["ArtikelVKNetto"]);
		$artikel->fMwSt = realEscape($_POST["ArtikelMwSt"]);
		$artikel->cAnmerkung = realEscape($_POST["ArtikelAnmerkung"]);
		$artikel->nLagerbestand = max(realEscape($_POST["ArtikelLagerbestand"]),0);
		$artikel->cEinheit = realEscape($_POST["ArtikelEinheit"]);
		$artikel->nMindestbestellmaenge = realEscape($_POST["ArtikelMindBestell"]);
		$artikel->cBarcode = realEscape($_POST["ArtikelBarcode"]);
		$artikel->fVKHaendlerBrutto = realEscape($_POST["ArtikelVKHaendlerBrutto"]);
		$artikel->fVKHaendlerNetto = realEscape($_POST["ArtikelVKHaendlerNetto"]);
		$artikel->cTopArtikel = realEscape($_POST["TopAngebot"]);
		$artikel->fGewicht = realEscape($_POST["Gewicht"]);
		$artikel->cNeu = realEscape($_POST["Neu"]);
		$artikel->cKurzBeschreibung = realEscape($_POST["ArtikelKurzBeschreibung"]);
		$artikel->fUVP = realEscape($_POST["ArtikelUVP"]);
		$artikel->cHersteller = realEscape(htmlentities($_POST["Hersteller"]));
		$startseite=0;
		if ($artikel->cTopArtikel=="Y")
                  $startseite = 1;
		$shipping_status=0;
		if ($GLOBALS['einstellungen']->shipping_status_id>0)
			$shipping_status=$GLOBALS['einstellungen']->shipping_status_id;
		//update oder insert?
		$products_id = getFremdArtikel($artikel->kArtikel);
      if ($products_id > 0){
        //update
        $products_attributestable = $oostable['products_attributes'];
        $dbconn->Execute("DELETE FROM $products_attributestable WHERE products_id=".$products_id);

        $products_to_categoriestable = $oostable['products_to_categories'];
        $dbconn->Execute("DELETE FROM $products_to_categoriestable WHERE products_id=".$products_id);

        //evtl. andere MwSt?
        $products_tax_class_id = holeSteuerId($artikel->fMwSt);
        //evtl. neuer Hersteller?
        $manufacturers_id = holeHerstellerId($artikel->cHersteller);

        $productstable = $oostable['products'];
        $dbconn->Execute("UPDATE $productstable
                          SET products_model=\"$artikel->cArtNr\",
                              products_price=\"$artikel->fVKNetto\",
                              products_tax_class_id=\"$products_tax_class_id\",
                              products_quantity=\"$artikel->nLagerbestand\",
                              products_ean=\"$artikel->cBarcode\",
                              products_weight=\"$artikel->fGewicht\",
                              manufacturers_id=\"$manufacturers_id\",
                              products_status=1
                          WHERE products_id=".$products_id);

        $products_descriptiontable = $oostable['products_description'];
        $dbconn->Execute("UPDATE $products_descriptiontable
                          SET products_name=\"$artikel->cName\",
                              products_description=\"$artikel->cBeschreibung\",
                              products_short_description=\"$artikel->cKurzBeschreibung\",
                              products_keywords=\"\", products_meta_title=\"\",
                              products_meta_description=\"\", products_meta_keywords=\"\",
                              products_url=\"\"
                          WHERE products_id=".$products_id." AND
                                language_id=".$einstellungen->languages_id);
			//kundengrp preise
			insertPreise($products_id);
      } else {
        //insert
        //hole Mwst classId
        $products_tax_class_id = holeSteuerId($artikel->fMwSt);
        //setze Hersteller, falls es ihn noch nicht gibt
        $manufacturers_id = holeHerstellerId($artikel->cHersteller);

        $productstable = $oostable['products'];
        $dbconn->Execute("INSERT INTO $productstable 
                         (products_model,
                          products_price,
                          products_tax_class_id,
                          products_quantity,
                          products_ean,
                          products_weight,
                          manufacturers_id,
                          products_status) VALUES (\"".$artikel->cArtNr."\",
                                                       $artikel->fVKNetto,
                                                       $products_tax_class_id,
                                                       $artikel->nLagerbestand,
                                                   \"".$artikel->cBarcode."\",
                                                       $artikel->fGewicht,
                                                       $manufacturers_id,
                                                    1)");
        //hole id
        $products_id = $dbconn->Insert_ID();

				//mssen Preise in spezielle tabellen?
				$products_id=$products_id_arr[0];
				insertPreise($products_id_arr[0]);

        $products_descriptiontable = $oostable['products_description'];
        $dbconn->Execute("INSERT INTO $products_descriptiontable 
                          (products_id,
                          products_name,
                          products_description,
                          products_short_description,
                           language_id) VALUES (".$products_id.",
                                                 \"".$artikel->cName."\",
                                                 \"".$artikel->cBeschreibung."\",
                                                  \"".$artikel->cKurzBeschreibung."\",
                                                  $einstellungen->languages_id)");
				setMappingArtikel($artikel->kArtikel,$products_id);

      }
      //VPE
      if ($products_id>0) {
			$products_vpe_id=0;
			//gibt es schon so einen products_vpe?
			$cur_query = xtc_db_query("SELECT products_vpe_id FROM products_vpe WHERE language_id=".$einstellungen->languages_id." AND  products_vpe_name=\"".$artikel->cEinheit."\"");
			$products_vpe_id_arr = mysql_fetch_row($cur_query);
			if ($products_vpe_id_arr[0]>0)
			{
				$products_vpe_id=$products_vpe_id_arr[0];
			}
			else 
			{
				//fge neuen Shippingstatus ein
				$cur_query = xtc_db_query("SELECT max(products_vpe_id) FROM products_vpe");
				$max_shipping_products_vpe_arr = mysql_fetch_row($cur_query);
				$products_vpe_id = $max_shipping_products_vpe_arr[0]+1;
				xtc_db_query("INSERT INTO products_vpe (products_vpe_id, language_id, products_vpe_name) VALUES ($products_vpe_id, $einstellungen->languages_id, \"$artikel->cEinheit\")");
			}
                  $productstable = $oostable['products'];
			xtc_db_query("UPDATE $productstable SET products_vpe=".$products_vpe_id." WHERE products_id=".$products_id);
		}
 	}
	else
		$return=5;

	if (intval($_POST["action"]) == 3 && intval($_POST['KeyArtikel']))
	{
		$products_id = getFremdArtikel(intval($_POST['KeyArtikel']));
		if ($products_id>0)

                  $productstable = $oostable['products'];
			xtc_db_query("UPDATE $productstable SET products_status=0 WHERE products_id=".$products_id);
		$return = 0;
	}
}

echo($return);
logge($return);

function insertPreise($products_id)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$personalOfferTable = "personal_offers_by_customers_status_";
	$endKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingEndkunde);
	foreach ($endKunden_arr as $customers_status_id)
	{
		if ($customers_status_id>0)
		{
			$table = $personalOfferTable.$customers_status_id;
			xtc_db_query("DELETE FROM $table WHERE products_id=".$products_id);
			xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,1,".floatval($_POST["ArtikelVKNetto"]).")");
			if (intval($_POST["PAnz1"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["PAnz1"]).",".floatval($_POST["PPreis1"]).")");
			if (intval($_POST["PAnz2"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["PAnz2"]).",".floatval($_POST["PPreis2"]).")");
			if (intval($_POST["PAnz3"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["PAnz3"]).",".floatval($_POST["PPreis3"]).")");
			if (intval($_POST["PAnz4"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["PAnz4"]).",".floatval($_POST["PPreis4"]).")");
			if (intval($_POST["PAnz5"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["PAnz5"]).",".floatval($_POST["PPreis5"]).")");
		}
	}
	$haendlerKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingHaendlerkunde);
	foreach ($haendlerKunden_arr as $customers_status_id)
	{
		if ($customers_status_id>0)
		{
			$table = $personalOfferTable.$customers_status_id;
			xtc_db_query("DELETE FROM $table WHERE products_id=".$products_id);
			xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,1,".floatval($_POST["ArtikelVKHaendlerNetto"]).")");
			if (intval($_POST["HAnz1"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["HAnz1"]).",".floatval($_POST["HPreis1"]).")");
			if (intval($_POST["HAnz2"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["HAnz2"]).",".floatval($_POST["HPreis2"]).")");
			if (intval($_POST["HAnz3"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["HAnz3"]).",".floatval($_POST["HPreis3"]).")");
			if (intval($_POST["HAnz4"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["HAnz4"]).",".floatval($_POST["HPreis4"]).")");
			if (intval($_POST["HAnz5"])>0)
				xtc_db_query("INSERT INTO $table (products_id, quantity, personal_offer) VALUES ($products_id,".intval($_POST["HAnz5"]).",".floatval($_POST["HPreis5"]).")");
		}
	}
}


  function holeHerstellerId($cHersteller) {

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
                                                                ".$GLOBALS['einstellungen']->languages_id.")");
         return $id;
      }
    }
    return 0;
  }


function holeSteuerId($MwSt)
{

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	//existiert so ein Steuersatz ?
    $tax_ratestable = $oostable['tax_rates'];
	$cur_query = xtc_db_query("SELECT tax_class_id
                                   FROM $tax_ratestable
                                   WHERE tax_zone_id=".$GLOBALS['einstellungen']->tax_zone_id."
                                     AND tax_rate=".$MwSt);
	$tax = mysql_fetch_object($cur_query);
	if ($tax->tax_class_id>0)
		return $tax->tax_class_id;
	else 
	{
       $tax_classtable = $oostable['tax_class'];
		xtc_db_query("INSERT INTO $tax_classtable (tax_class_title, date_added) VALUES (\"eazySales Steuerklasse ".$MwSt."%\", now())");
		$query = xtc_db_query("SELECT LAST_INSERT_ID()");
		$tax_class_id_arr = mysql_fetch_row($query);

       $tax_ratestable = $oostable['tax_rates'];
		xtc_db_query("INSERT INTO $tax_ratestable (tax_zone_id, tax_class_id, tax_priority, tax_rate, date_added) VALUES (".$GLOBALS['einstellungen']->tax_zone_id.",".$tax_class_id_arr[0].", ".$GLOBALS['einstellungen']->tax_priority.", ".$MwSt.", now())");
		return $tax_class_id_arr[0];
	}
}
?>