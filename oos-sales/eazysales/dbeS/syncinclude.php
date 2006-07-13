<?php
/* ----------------------------------------------------------------------
   $Id: syncinclude.php,v 1.10 2006/07/13 03:05:50 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: syncinclude.php,v 1.0 14.06.06
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
 * eazySales_Connector/dbeS/syncinclude.php
 * Tools fr Sync
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 14.06.06
*/

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  if(!defined('ES_ENABLE_LOGGING')) {
    define('ES_ENABLE_LOGGING', 0);
  }

  if(!defined('SHOP_ROOT')) {
    define('SHOP_ROOT', dirname(__FILE__) . '/../../../');
  }

  require SHOP_ROOT. '/includes/config.php';

  require SHOP_ROOT . OOS_INCLUDES . 'oos_tables.php';
  require '../admin/oos_tables.php';

  require SHOP_ROOT . OOS_FUNCTIONS . 'function_kernel.php';

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
   * Authentifiziert die Anfrage
   *
   * @return Bool true, wenn Auth ok, sonst false
   */
  function auth() {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $cName = $_POST['userID'];
    $cPass = $_POST['userPWD'];

    $eazysales_synctable = $oostable['eazysales_sync'];
    $query = "SELECT cName, cPass
              FROM $eazysales_synctable";
    $result = $dbconn->GetRow($query);
    if ($cName == $result['cName'] && $cPass == $result['cPass']) {
      return true;
    }

    return false;
  }

  /**
   * Gibt einen vardump eines Objekts aus.
   *
   * @param Object $vardump Objekt, das gedumpt werden soll
   * @param int $key Schlssel
   * @param int $level Aktuellw Tiefe
   * @param String $return Rckgabestring
   * @return String verbesserten Vardump
   */
  function Dump($vardump) {

    if (gettype($vardump)!="object" && gettype($vardump)!="array") {
      $return.= $vardump;
    } elseif (gettype($vardump)=="object") {
      foreach(get_object_vars($vardump) as $key => $value) {
        $return.= $key." => ".Dump($value).", ";
      }
    } elseif (gettype($vardump)=="array") {
      foreach ($vardump as $key => $value)
        $return.= $key." => ".Dump($value).", ";
      }
    }

    if ($return{strlen($return)-2}==',') {
      return substr($return,0,strlen($return)-2)." ";
    } else [
      return $return;
    }
  }


  /**
   * F�t Anfhrungszeichen vorne und am Ende an, sobald die Variable nicht leer.
   *
   * @param mixed $value
   * @return $value mit Anfhrungszeichen vorne und hinten. Falls $value leer, werden diese Zeichen nicht hinzugefgt.
   */
  function CSVkonform($value) {
     if (strlen($value)>0)
       return '"'.str_replace('"','""',$value).'"';
   }


  function datetime2germanDate($datetime) {

     list ($datum, $uhrzeit) = split(" ",$datetime);
     list ($jahr, $monat, $tag) = split ("-",$datum);
     list ($std, $min, $sec) = split (":",$uhrzeit);
     return $tag.'.'.$monat.'.'.$jahr.' '.$std.':'.$min.':'.$sec;
   }


  function unhtmlentities($string) {

     // replace numeric entities
     $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
     $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
     // replace literal entities
     $trans_tbl = get_html_translation_table(HTML_ENTITIES);
     $trans_tbl = array_flip($trans_tbl);
     return strtr($string, $trans_tbl);
  }


  function setMappingArtikel ($eS_key, $mein_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eS_key = intval($eS_key);
    $mein_key = intval($mein_key);

    if ($mein_key && $eS_key) {
      $eazysales_martikeltable = $oostable['eazysales_martikel'];
      $query = "SELECT products_id
                FROM $eazysales_martikeltable
                WHERE products_id = '" . intval($mein_key) . "'";
      $result = $dbconn->Execute($query);
      if ($result->RecordCount() > 0) {
        return true;
      } else {
        $eazysales_martikeltable = $oostable['eazysales_martikel'];
        $query = "INSERT INTO $eazysales_martikeltable
                 (products_id,
                  kArtikel)
                  VALUES (" . $dbconn->qstr($mein_key) . ','
                            . $dbconn->qstr($eS_key) . ")";
        $result = $dbconn->Execute($query);
      }
    }
    return true;
  }

  function setMappingKategorie ($eS_key, $mein_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eS_key = intval($eS_key);
    $mein_key = intval($mein_key);

    if ($mein_key && $eS_key) {
      $eazysales_mkategorietable = $oostable['eazysales_mkategorie'];
      $query = "SELECT categories_id
                FROM $eazysales_mkategorietable
                WHERE categories_id = '" . intval($mein_key) . "'";
      $result = $dbconn->Execute($query);
      if ($result->RecordCount() > 0) {
        return true;
      } else {
        $eazysales_mkategorietable = $oostable['eazysales_mkategorie'];
        $query = "INSERT INTO $eazysales_mkategorietable
                  (categories_id,
                   kKategorie)
                   VALUES (" . $dbconn->qstr($mein_key) . ','
                             . $dbconn->qstr($eS_key) . ")";
        $result = $dbconn->Execute($query);
      }
    }
    return true;
  }

  function setMappingEigenschaft ($eS_key, $mein_key, $kArtikel) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eS_key = intval($eS_key);
    $mein_key = intval($mein_key);

    if ($mein_key && $eS_key && $kArtikel) {
      $eazysales_mvariationtable = $oostable['eazysales_mvariation'];
      $dbconn->Execute("DELETE FROM $eazysales_mvariationtable WHERE kEigenschaft = '" . intval($eS_key) . "'");

      $query = "INSERT INTO $eazysales_mvariationtable
               (kEigenschaft,
                products_options_id,
                kArtikel)
                VALUES (" . $dbconn->qstr($eS_key) . ','
                          . $dbconn->qstr($mein_key) . ','
                          . $dbconn->qstr($kArtikel) . ")";
      $result = $dbconn->Execute($query);

    }
  }

  function setMappingBestellPos ($mein_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $mein_key = intval($mein_key);

    $eazysales_mbestellpostable = $oostable['eazysales_mbestellpos'];
    $dbconn->Execute("DELETE FROM $eazysales_mbestellpostable WHERE orders_products_id= '" . intval($mein_key) . "'");

    $query = "INSERT INTO $eazysales_mbestellpostable (orders_products_id) VALUES (" . $dbconn->qstr($mein_key) . ")";
    $result = $dbconn->Execute($query);

    $insert_id = $dbconn->Insert_ID();
    return $insert_id;
  }

  function setMappingEigenschaftsWert ($eS_key, $mein_key, $kArtikel) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eS_key = intval($eS_key);
    $mein_key = intval($mein_key);

    if ($mein_key && $eS_key) {
      $eazysales_mvariationswerttable = $oostable['eazysales_mvariationswert'];
		logExtra("DELETE FROM $eazysales_mvariationswerttable WHERE kEigenschaftsWert=".$eS_key);
		xtc_db_query("DELETE FROM $eazysales_mvariationswerttable WHERE kEigenschaftsWert=".$eS_key);

		//ist mein_key schon drin?
      $eazysales_mvariationswerttable = $oostable['eazysales_mvariationswert'];
		$cur_query = xtc_db_query("SELECT products_attributes_id FROM $eazysales_mvariationswerttable WHERE kArtikel=$kArtikel AND products_attributes_id=".$mein_key);
		logExtra("SELECT products_attributes_id FROM $eazysales_mvariationswerttable WHERE kArtikel=$kArtikel AND products_attributes_id=".$mein_key);
		$prod = mysql_fetch_object($cur_query);
		if ($prod->products_id>0)
			return "";
		} else {
         $eazysales_mvariationswerttable = $oostable['eazysales_mvariationswert'];
			logExtra("INSERT INTO $eazysales_mvariationswerttable (products_attributes_id, kEigenschaftsWert, kArtikel) VALUES ($mein_key,$eS_key,$kArtikel)");
			xtc_db_query("INSERT INTO $eazysales_mvariationswerttable (products_attributes_id, kEigenschaftsWert, kArtikel) VALUES ($mein_key,$eS_key,$kArtikel)");
		}
      }
    }

  function getFremdArtikel($eS_key) {
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_martikeltable = $oostable['eazysales_martikel'];
	$cur_query = xtc_db_query("SELECT products_id
                                   FROM $eazysales_martikeltable
                                   WHERE kArtikel=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_id;
}

  function getEsArtikel($mein_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_martikeltable = $oostable['eazysales_martikel'];

	$cur_query = xtc_db_query("SELECT kArtikel
                                   FROM $eazysales_martikeltable
                                   WHERE products_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kArtikel;
}

  function getFremdKategorie($eS_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mkategorietable = $oostable['eazysales_mkategorie'];

	$cur_query = xtc_db_query("SELECT categories_id
                                   FROM $eazysales_mkategorietable
                                   WHERE kKategorie=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->categories_id;
}

  function getEsKategorie($mein_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mkategorietable = $oostable['eazysales_mkategorie'];

	$cur_query = xtc_db_query("SELECT kKategorie
                                   FROM $eazysales_mkategorietable
                                   WHERE categories_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kKategorie;
}

  function getFremdBestellPos($eS_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mbestellpostable = $oostable['eazysales_mbestellpos'];

	$cur_query = xtc_db_query("SELECT orders_products_id
                                   FROM $eazysales_mbestellpostable
                                   WHERE kBestellPos=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->orders_products_id;
}

  function getEsEigenschaft($mein_key, $kArtikel) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mvariationtable = $oostable['eazysales_mvariation'];

	$cur_query = xtc_db_query("SELECT kEigenschaft 
                                   FROM $eazysales_mvariationtable
                                   WHERE kArtikel=".$kArtikel." 
                                     AND products_options_id=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kEigenschaft;
}

  function getFremdEigenschaft($eS_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mvariationtable = $oostable['eazysales_mvariation'];

	$cur_query = xtc_db_query("SELECT products_options_id
                                   FROM $eazysales_mvariationtable
                                   WHERE kEigenschaft=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_options_id;
}

  function getEigenschaftsArtikel($eS_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mvariationtable = $oostable['eazysales_mvariation'];

	$cur_query = xtc_db_query("SELECT kArtikel
                                   FROM $eazysales_mvariationtable
                                   WHERE kEigenschaft=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kArtikel;
}

  function getFremdEigenschaftsWert($eS_key) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mvariationswerttable = $oostable['eazysales_mvariationswert'];

	$cur_query = xtc_db_query("SELECT products_attributes_id
                                   FROM $eazysales_mvariationswerttable
                                   WHERE kEigenschaftsWert=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_attributes_id;
}

  function getEsEigenschaftsWert($mein_key, $kArtikel) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_mvariationswerttable = $oostable['eazysales_mvariationswert'];

	$cur_query = xtc_db_query("SELECT kEigenschaftsWert
                                   FROM $eazysales_mvariationswerttable
                                   WHERE kArtikel=$kArtikel
                                     AND products_attributes_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kEigenschaftsWert;
}

/**
 * real mysql escape mysql escape
 * @access public
 * @param string $ausdruck Ausdruck, der escaped fr mysql werden soll
 * @return escaped expression
 */
  function realEscape ($ausdruck) {

	if (get_magic_quotes_gpc())
		return mysql_real_escape_string(stripslashes($ausdruck));
	else
		return mysql_real_escape_string($ausdruck);
}

  function logExtra($entry) {

	if (ES_ENABLE_LOGGING!=1)
		return "";
	$logfilename = "logs/".basename($_SERVER['REQUEST_URI'],".php").".log";
	$logfile = fopen($logfilename, 'a');
	fwrite($logfile,"\n[#######Extra Log##########] [".date('m.d.y H:i:s')."]\n".$entry);
	fclose($logfile);
}

  function logge($return) {

	if (ES_ENABLE_LOGGING!=1)
		return "";
	$logfilename = "logs/".basename($_SERVER['REQUEST_URI'],".php").".log";
	$logfile = fopen($logfilename, 'a');
	fwrite($logfile,"\n[".date('m.d.y H:i:s')."] - Ret: $return\n".Dump($_POST));
	fclose($logfile);
}

//get tax 4 product
  function get_tax($products_tax_class_id) {

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $tax_ratestable = $oostable['tax_rates'];

	//get tax class
	$taxclass_query = xtc_db_query("SELECT *
                                        FROM $tax_ratestable
                                        WHERE tax_class_id=".$products_tax_class_id."
                                          AND tax_zone_id=".$GLOBALS['einstellungen']->tax_zone_id);
	$tax = mysql_fetch_object($taxclass_query);
	return ($tax->tax_rate);
}
?>