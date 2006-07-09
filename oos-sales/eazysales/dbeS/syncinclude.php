<?php
/* ----------------------------------------------------------------------
   $Id: syncinclude.php,v 1.5 2006/07/09 02:20:22 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: syncinclude.php,v 1.0 14.06.06
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

require '../paths.php';

//get DB Connecion
// include server parameters
require_once (DOCROOT_XTC_PATH.'admin/includes/configure.php');
require_once (DIR_FS_INC . 'xtc_db_connect.inc.php');
require_once (DIR_FS_INC . 'xtc_db_query.inc.php');

xtc_db_connect() or die('Kann Datenbankverbindung nicht herstellen! �erprfen Sie den DOCROOT_XTC_PATH im eazySales_Connector/paths.php Script Zeile 15. Der Pfad muss entweder relativ oder absolut auf das Rootverzeichnis Ihres Shops zeigen (meist <i>xtcommerce</i>).');

define ('ES_ENABLE_LOGGING',0);

/**
 * Authentifiziert die Anfrage
 *
 * @return Bool true, wenn Auth ok, sonst false
 */
function auth()
{
	$cName = $_POST["userID"];
	$cPass = $_POST["userPWD"];

	$cur_query = xtc_db_query("SELECT * FROM eazysales_sync");
	$loginDaten = mysql_fetch_object($cur_query);
	if ($cName == $loginDaten->cName && $cPass == $loginDaten->cPass)
		return true;

	return false;
}

/**
 * Gibt einen vardump eines Objekts aus, der sich besser loggen l�st.
 *
 * @param Object $vardump Objekt, das gedumpt werden soll
 * @param int $key Schlssel
 * @param int $level Aktuellw Tiefe
 * @param String $return Rckgabestring
 * @return String verbesserten Vardump
 */
function Dump($vardump)
{
	if (gettype($vardump)!="object" && gettype($vardump)!="array")
		$return.= $vardump;
	elseif (gettype($vardump)=="object")
	{
		foreach(get_object_vars($vardump) as $key => $value)
		{
			$return.= $key." => ".Dump($value).", ";
		}
	}
	elseif (gettype($vardump)=="array")
	{
		foreach ($vardump as $key => $value)
			$return.= $key." => ".Dump($value).", ";
	}
	if ($return{strlen($return)-2}==',')
		return substr($return,0,strlen($return)-2)." ";
	else 
		return $return;
}

/**
 * Fgt Anfhrungszeichen vorne und am Ende an, sobald die Variable nicht leer.
 *
 * @param mixed $value
 * @return $value mit Anfhrungszeichen vorne und hinten. Falls $value leer, werden diese Zeichen nicht hinzugefgt.
 */
function CSVkonform($value)
{
	if (strlen($value)>0)
		return '"'.str_replace('"','""',$value).'"';
}

function datetime2germanDate($datetime)
{
	list ($datum, $uhrzeit) = split(" ",$datetime);
	list ($jahr, $monat, $tag) = split ("-",$datum);
	list ($std, $min, $sec) = split (":",$uhrzeit);
	return $tag.'.'.$monat.'.'.$jahr.' '.$std.':'.$min.':'.$sec;
}

function unhtmlentities($string)
{
   // replace numeric entities
   $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
   $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
   // replace literal entities
   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
   $trans_tbl = array_flip($trans_tbl);
   return strtr($string, $trans_tbl);
}

function setMappingArtikel ($eS_key, $mein_key)
{
	$eS_key = intval($eS_key);
	$mein_key = intval($mein_key);
	if ($mein_key && $eS_key)
	{
		//ist mein_key schon drin?
		$cur_query = xtc_db_query("SELECT products_id FROM eazysales_martikel WHERE products_id=".$mein_key);
		$prod = mysql_fetch_object($cur_query);
		if ($prod->products_id>0)
			return "";
		else 
		{
			xtc_db_query("INSERT INTO eazysales_martikel (products_id, kArtikel) values ($mein_key,$eS_key)");
		}
	}
}

function setMappingKategorie ($eS_key, $mein_key)
{
	$eS_key = intval($eS_key);
	$mein_key = intval($mein_key);
	if ($mein_key && $eS_key)
	{
		//ist mein_key schon drin?
		$cur_query = xtc_db_query("SELECT categories_id FROM eazysales_mkategorie WHERE categories_id=".$mein_key);
		$prod = mysql_fetch_object($cur_query);
		if ($prod->categories_id>0)
			return "";
		else 
		{
			xtc_db_query("INSERT INTO eazysales_mkategorie (categories_id, kKategorie) values ($mein_key,$eS_key)");
		}
	}
}

function setMappingEigenschaft ($eS_key, $mein_key, $kArtikel)
{
	$eS_key = intval($eS_key);
	$mein_key = intval($mein_key);
	if ($mein_key && $eS_key && $kArtikel)
	{
		xtc_db_query("DELETE FROM eazysales_mvariation WHERE kEigenschaft=".$eS_key);
		xtc_db_query("INSERT INTO eazysales_mvariation (kEigenschaft,products_options_id,kArtikel) values ($eS_key, $mein_key, $kArtikel)");
	}
}

function setMappingBestellPos ($mein_key)
{
	$mein_key = intval($mein_key);
	xtc_db_query("DELETE FROM eazysales_mbestellpos WHERE orders_products_id=".$mein_key);
	xtc_db_query("INSERT INTO eazysales_mbestellpos (orders_products_id) values ($mein_key)");
	$query = xtc_db_query("SELECT LAST_INSERT_ID()");
	$id_arr = mysql_fetch_row($query);
	return $id_arr[0];
}

function setMappingEigenschaftsWert ($eS_key, $mein_key, $kArtikel)
{
	$eS_key = intval($eS_key);
	$mein_key = intval($mein_key);
	if ($mein_key && $eS_key)
	{
		logExtra("DELETE FROM eazysales_mvariationswert WHERE kEigenschaftsWert=".$eS_key);
		xtc_db_query("DELETE FROM eazysales_mvariationswert WHERE kEigenschaftsWert=".$eS_key);
		//ist mein_key schon drin?
		$cur_query = xtc_db_query("SELECT products_attributes_id FROM eazysales_mvariationswert WHERE kArtikel=$kArtikel AND products_attributes_id=".$mein_key);
		logExtra("SELECT products_attributes_id FROM eazysales_mvariationswert WHERE kArtikel=$kArtikel AND products_attributes_id=".$mein_key);
		$prod = mysql_fetch_object($cur_query);
		if ($prod->products_id>0)
			return "";
		else 
		{
			logExtra("INSERT INTO eazysales_mvariationswert (products_attributes_id, kEigenschaftsWert, kArtikel) values ($mein_key,$eS_key,$kArtikel)");
			xtc_db_query("INSERT INTO eazysales_mvariationswert (products_attributes_id, kEigenschaftsWert, kArtikel) values ($mein_key,$eS_key,$kArtikel)");
		}
	}
}

function getFremdArtikel($eS_key)
{
	$cur_query = xtc_db_query("SELECT products_id FROM eazysales_martikel WHERE kArtikel=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_id;
}

function getEsArtikel($mein_key)
{
	$cur_query = xtc_db_query("SELECT kArtikel FROM eazysales_martikel WHERE products_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kArtikel;
}

function getFremdKategorie($eS_key)
{
	$cur_query = xtc_db_query("SELECT categories_id FROM eazysales_mkategorie WHERE kKategorie=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->categories_id;
}

function getEsKategorie($mein_key)
{
	$cur_query = xtc_db_query("SELECT kKategorie FROM eazysales_mkategorie WHERE categories_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kKategorie;
}

function getFremdBestellPos($eS_key)
{
	$cur_query = xtc_db_query("SELECT orders_products_id FROM eazysales_mbestellpos WHERE kBestellPos=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->orders_products_id;
}

function getEsEigenschaft($mein_key, $kArtikel)
{
	$cur_query = xtc_db_query("SELECT kEigenschaft FROM eazysales_mvariation WHERE kArtikel=".$kArtikel." AND products_options_id=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kEigenschaft;
}

function getFremdEigenschaft($eS_key)
{
	$cur_query = xtc_db_query("SELECT products_options_id FROM eazysales_mvariation WHERE kEigenschaft=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_options_id;
}

function getEigenschaftsArtikel($eS_key)
{
	$cur_query = xtc_db_query("SELECT kArtikel FROM eazysales_mvariation WHERE kEigenschaft=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kArtikel;
}

function getFremdEigenschaftsWert($eS_key)
{
	$cur_query = xtc_db_query("SELECT products_attributes_id FROM eazysales_mvariationswert WHERE kEigenschaftsWert=".$eS_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->products_attributes_id;
}

function getEsEigenschaftsWert($mein_key, $kArtikel)
{
	$cur_query = xtc_db_query("SELECT kEigenschaftsWert FROM eazysales_mvariationswert WHERE kArtikel=$kArtikel AND products_attributes_id=".$mein_key);
	$prod = mysql_fetch_object($cur_query);
	return $prod->kEigenschaftsWert;
}

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

function logExtra($entry)
{
	if (ES_ENABLE_LOGGING!=1)
		return "";
	$logfilename = "logs/".basename($_SERVER['REQUEST_URI'],".php").".log";
	$logfile = fopen($logfilename, 'a');
	fwrite($logfile,"\n[#######Extra Log##########] [".date('m.d.y H:i:s')."]\n".$entry);
	fclose($logfile);
}

function logge($return)
{
	if (ES_ENABLE_LOGGING!=1)
		return "";
	$logfilename = "logs/".basename($_SERVER['REQUEST_URI'],".php").".log";
	$logfile = fopen($logfilename, 'a');
	fwrite($logfile,"\n[".date('m.d.y H:i:s')."] - Ret: $return\n".Dump($_POST));
	fclose($logfile);
}

//get tax 4 product
function get_tax($products_tax_class_id)
{
	//get tax class
	$taxclass_query = xtc_db_query("SELECT * FROM tax_rates WHERE tax_class_id=".$products_tax_class_id." AND tax_zone_id=".$GLOBALS['einstellungen']->tax_zone_id);
	$tax = mysql_fetch_object($taxclass_query);
	return ($tax->tax_rate);
}
?>