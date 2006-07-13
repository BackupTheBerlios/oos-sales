<?php
/* ----------------------------------------------------------------------
   $Id: konfiguration.php,v 1.14 2006/07/13 03:32:11 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: konfiguration.php,v 1.0 14.06.06
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

require 'admininclude.php';
require 'adminTemplates.php';

$adminsession = new AdminSession();

if ($_SESSION["loggedIn"]!=1)
{
	header('Location: index.php');
	exit;
}
if ($_POST['update']==1)
updateKonfig();

zeigeKopf();
zeigeLinks($_SESSION["loggedIn"]);
zeigeKonfigForm();
zeigeFuss();

function zeigeKonfigForm()
{

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
    $cur_query = xtc_db_query("SELECT currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL,
                                      tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,
                                      cat_listing_template, cat_category_template, cat_sorting, cat_sorting2,
                                      prod_product_template, prod_options_template, StatusAbgeholt, StatusVersendet
                               FROM $eazysales_einstellungenstable");
	$einstellungen = mysql_fetch_object($cur_query);	

    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $order_array = array(array('id' => 'p.products_price','text'=>'Artikelpreis'),
                         array('id' => 'pd.products_name','text'=>'Artikelname'),
                         array('id' => 'p.products_ordered','text'=>'Bestellte Artikel'),
                         array('id' => 'p.products_sort','text'=>'Reihung'),
                         array('id' => 'p.products_weight','text'=>'Gewicht'),
                         array('id' => 'p.products_quantity','text'=>'Lagerbestand'));

    $order_array2 = array(array('id' => 'ASC','text'=>'Aufsteigend'),
                          array('id' => 'DESC','text'=>'Absteigend'));

    //defaultwerte setzen
    if (!$einstellungen->shopURL) $einstellungen->shopURL = OOS_HTTP_SERVER . OOS_SHOP;
    if (!$einstellungen->tax_priority) $einstellungen->tax_priority = 1;
    if (!$einstellungen->versandMwst) $einstellungen->versandMwst = 16;

    if (!$einstellungen->languages_id) {
      $configurationtable = $oostable['configuration'];
      $query = "SELECT configuration_value
                FROM $configurationtable
                WHERE configuration_key = 'DEFAULT_LANGUAGE'";
      $def_lang = $dbconn->GetOne($query);

      $languagestable = $oostable['languages'];
      $query = "SELECT languages_id
                FROM $languagestable
                WHERE iso_639_2 = '" . $def_lang . "'";
      $langID = $dbconn->GetOne($query);
      $einstellungen->languages_id = $langID;
    }

    if (!$einstellungen->mappingEndkunde) {
      $configurationtable = $oostable['configuration'];
      $query = "SELECT configuration_value
                FROM $configurationtable
                WHERE configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'";
      $def_userstatus = $dbconn->GetOne($query);
      $einstellungen->mappingEndkunde = $def_userstatus;

      $configurationtable = $oostable['configuration'];
      $cur_query = "SELECT configuration_value
                    FROM $configurationtable
                    WHERE configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID_GUEST'";
      $def_userstatus_guest = $dbconn->GetOne($query);
      $einstellungen->mappingEndkunde .= ';' . $def_userstatus_guest;
    }


	$mappingEndkunde_arr = explode (";",$einstellungen->mappingEndkunde);
	$mappingHaendlerkunde_arr = explode (";",$einstellungen->mappingHaendlerkunde);
	

      echo '
	<td bgcolor="#ffffff" style="border-color:#222222; border-width:1px; border-style:solid; border-top-width:0px; border-bottom-width:0px;" valign="top" align="center"><br />
	<table cellspacing="0" cellpadding="0" width="96%">
	<tr><td class="content_header" align="center"><h3>eazySales Connector Installation</h3></td></tr>
	<tr><td class="content" align="center"><br />
		F&uuml;r den reibungslosen Im-/ und Export von Daten zwischen <a href="http://www.jtl-software.de">eazySales</a> und Ihrem Shop, m&uuml;ssen einige Einstellungen als Standard gesetzt sein.<br /><br />
		<table cellspacing="0" cellpadding="0" width="580">
		<tr>
			<td class="unter_content_header">&nbsp;<b>Einstellungen</b></td>
		</tr>
		<tr>
			<td class="content" align="center">
				Hilfe zu den einzelnen Einstellungm&ouml;glichkeiten finden Sie unter <a href="http://www.jtl-software.de/eazySales_connector.php" target="_blank">eazySales Connector Konfigurationshilfe</a>.<br />
				<form action="index.php" method="post" name="konfig">
				<input type="hidden" name="install" value="1">
				<table cellspacing="0" cellpadding="10" width="100%">
				<tr>
					<td><b>Shop URL</b></td><td><input type="text" name="shopurl" size="50" class="konfig" value="'.$einstellungen->shopURL.'"></td>
				</tr>
				<tr>
					<td><b>Standardw&auml;hrung</b></td><td><select name="waehrung">
      ';


      $currenciestable = $oostable['currencies'];
      $query = "SELECT currencies_id, title, code
                FROM $currenciestable";
      $result =& $dbconn->Execute($query);

      while ($currency = $result->fields) {
        echo '<option value="' . $currency['currencies_id'] . '" ';  if ($currency['currencies_id'] == $einstellungen->currencies_id) echo ' selected="selected"'; echo '>' . $currency['title'] . '</option>';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				<tr>
					<td><b>Standardsprache</b></td><td><select name="sprache">
      ';

      $languagestable = $oostable['languages'];
      $query = "SELECT languages_id, name, iso_639_2
                FROM $languagestable";
      $result =& $dbconn->Execute($query);

      while ($lang = $result->fields) {
        echo '<option value="' . $lang['languages_id'] . '" '; if ($lang['languages_id'] == $einstellungen->languages_id) echo ' selected="selected"'; echo '>' . $lang['name'] . '</option>';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				<tr>
					<td>Umsatzsteuer</td><td>&nbsp;</td>
				</tr>
				<tr>
					<td><b>Standard Steuerzone</b></td><td><select name="steuerzone">
      ';

      $geo_zonestable = $oostable['geo_zones'];
      $query ="SELECT geo_zone_id, geo_zone_name
               FROM $geo_zonestable";
      $result =& $dbconn->Execute($query);

      while ($zone = $result->fields) {
        echo '<option value="' . $zone['geo_zone_id'] . '" '; if ($zone['geo_zone_id'] == $einstellungen->tax_zone_id) echo ' selected="selected"'; echo '>' . $zone['geo_zone_name'] . '</option>';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				<tr>
					<td><b>Standard Steuerklasse*</b></td><td><select name="steuerklasse">
      ';

      $tax_classtable = $oostable['tax_class'];
      $query = "SELECT tax_class_id, tax_class_title
                FROM $tax_classtable";
      $result =& $dbconn->Execute($query);

      while ($klasse = $result->fields) {
        echo '<option value="' . $klasse['tax_class_id'] . '" '; if ($klasse['tax_class_id'] == $einstellungen->tax_class_id) echo ' selected="selected"'; echo '>' . $klasse['tax_class_title'] . '</option>';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				<tr>
					<td><b>Standard Steuersatzpriorit&auml;t</b></td><td><input type="text" name="prioritaet" size="50" class="konfig" style="width:30px;" value="'.$einstellungen->tax_priority.'"></td>
				</tr>
				<tr>
					<td><b>Steuersatz f&uuml;r Versandkosten</b></td><td><input type="text" name="versandMwst" size="50" class="konfig" style="width:30px;" value="'.$einstellungen->versandMwst.'"> %</td>
				</tr>
				<tr>
					<td>Bestellstatus&auml;nderungen</td><td>&nbsp;</td>
				</tr>
				<tr>
					<td><b>Sobald Bestellung erfolgreich in eazySales bernommen wird, Status setzen auf:</b></td><td><select name="StatusAbgeholt"><option value="0">Status nicht &auml;ndern</option>
      ';

      $orders_statustable = $oostable['orders_status'];
      $query = "SELECT orders_status_id, orders_status_name
                FROM $orders_statustable
                WHERE orders_languages_id = '" . $einstellungen->languages_id . "'
                ORDER BY orders_status_id";
      $result =& $dbconn->Execute($query);

      while ($status = $result->fields) {
        echo '<option value="' . $status['orders_status_id'] . '" '; if ($status['orders_status_id'] == $einstellungen->StatusAbgeholt) echo ' selected="selected"'; echo '>' . $status['orders_status_name'] . '</option>';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				<tr>
					<td><b>Sobald Bestellung in eazySales versandt wird, Status setzen auf</b></td><td><select name="StatusVersendet"><option value="0">Status nicht &auml;ndern</option>
      ';

      $orders_statustable = $oostable['orders_status'];
      $query = "SELECT orders_status_id, orders_status_name
                FROM $orders_statustable
                WHERE orders_languages_id = '" . $einstellungen->languages_id . "'
                ORDER BY orders_status_id";
      $result =& $dbconn->Execute($query);

      while ($status = $result->fields) {
        echo '<option value="' . $status['orders_status_id'] . '" '; if ($status['orders_status_id'] == $einstellungen->StatusVersendet) echo ' selected="selected"'; echo '>' . $status['orders_status_name'] . '</option>';


        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '</select></td>
				</tr>
				</table><br />
				eazySales kennt nur die Kundengruppen Endkunde und H&auml;ndlerkunde. Weisen Sie diesen Kundengruppen Ihre Shop-Kundengruppen zu - dies ist f&uuml;r die korrekte Preiszuordnung unerl&auml;sslich. Vergeben Sie nicht Ihre Kundengruppen doppelt.<br />
				<table cellspacing="0" cellpadding="10" width="100%">
				<tr>
					<td valign="top"><b>eazySales Endkunde</b></td>
					<td>
      ';

      $customers_statusstable = $oostable['customers_status'];
      $query = "SELECT customers_status_id, customers_status_name
                FROM $customers_statusstable
                WHERE customers_status_languages_id = '" . $einstellungen->languages_id . "'
                ORDER BY customers_status_id";
      $result =& $dbconn->Execute($query);

      while ($grp = $result->fields) {
        echo '<input type="checkbox" name="endkunde[]" value="' . $grp['customers_status_id'] . '"'; if (in_array( $grp['customers_status_id'], $mappingEndkunde_arr)) echo' checked="checked"'; echo'> ' . $grp['customers_status_name'] . '<br />';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '
					</td>
				</tr>
				<tr>
					<td valign="top"><b>eazySales H&auml;dlerkunde</b></td>
					<td>
      ';

      $customers_statusstable = $oostable['customers_status'];
      $query = "SELECT customers_status_id, customers_status_name
                FROM $customers_statusstable
                WHERE customers_status_languages_id = '" . $einstellungen->languages_id . "'
                ORDER BY customers_status_id";
      $result =& $dbconn->Execute($query);

      while ($grp = $result->fields) {
        echo '<input type="checkbox" name="haendlerkunde[]" value="' . $grp['customers_status_id'] . '"'; if (in_array( $grp['customers_status_id'], $mappingEndkunde_arr)) echo' checked="checked"'; echo'> ' . $grp['customers_status_name'] . '<br />';

        $result->MoveNext();
      }
      // Close result set
      $result->Close();

      echo '
					</td>
				</tr>
				</table><br />

				<table cellspacing="0" cellpadding="10" width="100%">
				<tr>
					<td valign="top"><b>Artikelsortierung</b></td><td><select name="cat_sorting">
      ';

      if (is_array($order_array)) {
        foreach ($order_array as $sortierung) {
           echo '<option value="'.$sortierung['id'].'" '; if ($sortierung['id']==$einstellungen->cat_sorting) echo ' selected="selected"'; echo '>'.$sortierung['text'].'</option>';
        }
      }
      echo '</select> <select name="cat_sorting2">';

      if (is_array($order_array2)) {
        foreach ($order_array2 as $sortierung) {
           echo '<option value="'.$sortierung['id'].'" '; if ($sortierung['id']==$einstellungen->cat_sorting2) echo ' selected="selected"'; echo '>'.$sortierung['text'].'</option>';
        }
      }
      echo '</select>	
					</td>
				</tr>
				</table><br />
					<input type="submit" value="Einstellungen speichern">
				</form>
			</td>
		</tr>
		</table><br />
		</td></tr>
	</table><br />
	</td>
       ';
 }

function updateKonfig()
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$mappingEndkunde="";
	$mappingHaendlerkunde="";
	if (is_array($_POST['endkunde']))
		$mappingEndkunde = implode(";",$_POST['endkunde']);
	if (is_array($_POST['haendlerkunde']))
		$mappingHaendlerkunde = implode(";",$_POST['haendlerkunde']);
	
	$shopurl = $_POST['shopurl']; if (!$shopurl) $shopurl="";
	$waehrung = $_POST['waehrung']; if (!$waehrung) $waehrung=0;
	$sprache = $_POST['sprache']; if (!$sprache) $sprache=0;
	$liefertermin = $_POST['liefertermin']; if (!$liefertermin) $liefertermin=0;
	$steuerzone = $_POST['steuerzone']; if (!$steuerzone) $steuerzone=0;
	$steuerklasse = $_POST['steuerklasse']; if (!$steuerklasse) $steuerklasse=0;
	$prioritaet = $_POST['prioritaet']; if (!$prioritaet) $prioritaet=0;
	$versandMwst = floatval($_POST['versandMwst']); if (!$versandMwst) $versandMwst=0;
	$cat_listing = $_POST['cat_listing']; if (!$cat_listing) $cat_listing="";
	$cat_template = $_POST['cat_template']; if (!$cat_template) $cat_template="";
	$cat_sorting = $_POST['cat_sorting']; if (!$cat_sorting) $cat_sorting="";
	$cat_sorting2 = $_POST['cat_sorting2']; if (!$cat_sorting2) $cat_sorting2="";
	$product_template = $_POST['product_template']; if (!$product_template) $product_template="";
	$option_template = $_POST['option_template']; if (!$option_template) $option_template="";
	$statusAbgeholt = $_POST['StatusAbgeholt']; if (!$statusAbgeholt) $statusAbgeholt=0;
	$statusVersandt = $_POST['StatusVersendet']; if (!$statusVersandt) $statusVersandt=0;
	

	xtc_db_query("DELETE FROM eazysales_einstellungen");
	xtc_db_query("INSERT INTO eazysales_einstellungen (StatusAbgeholt, StatusVersendet, currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL, tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,cat_listing_template,cat_category_template,cat_sorting,cat_sorting2,prod_product_template,prod_options_template) VALUES ($statusAbgeholt, $statusVersandt, $waehrung,$sprache,\"$mappingEndkunde\",\"$mappingHaendlerkunde\",\"$shopurl\",$steuerklasse,$steuerzone,$prioritaet,$liefertermin, ".floatval($versandMwst).",\"$cat_listing\",\"$cat_template\",\"$cat_sorting\",\"$cat_sorting2\",\"$product_template\",\"$option_template\")");
}


?>

