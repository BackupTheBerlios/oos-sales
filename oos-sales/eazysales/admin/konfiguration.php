<?php
/* ----------------------------------------------------------------------
   $Id: konfiguration.php,v 1.8 2006/07/09 03:18:58 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: konfiguration.php,v 1.0 14.06.06
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

    $cur_query = xtc_db_query("SELECT currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL,
                                      tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,
                                      cat_listing_template, cat_category_template, cat_sorting, cat_sorting2,
                                      prod_product_template, prod_options_template, StatusAbgeholt, StatusVersendet
                               FROM eazysales_einstellungen");
	$einstellungen = mysql_fetch_object($cur_query);	
	
	$cur_query = xtc_db_query("SELECT configuration_value
                                   FROM configuration
                                   WHERE configuration_key=\"CURRENT_TEMPLATE\"");
	$cur_template = mysql_fetch_object($cur_query);
	
	//Templategeschichten
	$product_listing_template_arr = getTemplateArray($cur_template, "product_listing");
	$category_listing_template_arr = getTemplateArray($cur_template, "categorie_listing");
	$productinfo_template_arr = getTemplateArray($cur_template, "product_info");
	$productoptions_template_arr = getTemplateArray($cur_template, "product_options");
	
	$order_array=array(array('id' => 'p.products_price','text'=>'Artikelpreis'),
				array('id' => 'pd.products_name','text'=>'Artikelname'),
				array('id' => 'p.products_ordered','text'=>'Bestellte Artikel'),
				array('id' => 'p.products_sort','text'=>'Reihung'),
				array('id' => 'p.products_weight','text'=>'Gewicht'),
				array('id' => 'p.products_quantity','text'=>'Lagerbestand'));
				
	$order_array2=array(array('id' => 'ASC','text'=>'Aufsteigend'),
				array('id' => 'DESC','text'=>'Absteigend'));
				
	//Templatesachen fr Produkte
	
				
	//defaultwerte setzen
	if (!$einstellungen->shopURL)
		$einstellungen->shopURL = HTTP_SERVER;
	if (!$einstellungen->tax_priority)
		$einstellungen->tax_priority = 1;
	if (!$einstellungen->versandMwst)
		$einstellungen->versandMwst = 16;
	if (!$einstellungen->languages_id)
	{
		$cur_query = xtc_db_query("SELECT configuration_value
                                           FROM configuration
                                           WHERE configuration_key=\"DEFAULT_LANGUAGE\"");
		$def_lang = mysql_fetch_object($cur_query);

		if ($def_lang->configuration_value)
		{
			$cur_query = xtc_db_query("SELECT languages_id
                                                   FROM languages
                                                   WHERE code=\"".$def_lang->configuration_value."\"");
			$langID = mysql_fetch_object($cur_query);
			$einstellungen->languages_id = $langID->languages_id;
		} else {
			//erstbeste Lang
			$cur_query = xtc_db_query("SELECT languages_id FROM languages");
			$langID = mysql_fetch_object($cur_query);
			$einstellungen->languages_id = $langID->languages_id;
		}
	}
	if (!$einstellungen->mappingEndkunde)
	{
		$cur_query = xtc_db_query("SELECT configuration_value 
                                           FROM configuration 
                                           WHERE configuration_key=\"DEFAULT_CUSTOMERS_STATUS_ID\"");
		$def_userstatus = mysql_fetch_object($cur_query);
		$einstellungen->mappingEndkunde=$def_userstatus->configuration_value;

		$cur_query = xtc_db_query("SELECT configuration_value 
                                           FROM configuration 
                                           WHERE configuration_key=\"DEFAULT_CUSTOMERS_STATUS_ID_GUEST\"");
		$def_userstatus_guest = mysql_fetch_object($cur_query);		
		$einstellungen->mappingEndkunde.=";".$def_userstatus_guest->configuration_value;
	}
	$mappingEndkunde_arr = explode (";",$einstellungen->mappingEndkunde);
	$mappingHaendlerkunde_arr = explode (";",$einstellungen->mappingHaendlerkunde);
	
	echo('
						<td bgcolor="#ffffff" style="border-color:#222222; border-width:1px; border-style:solid; border-top-width:0px; border-bottom-width:0px; border-left-width:0px;" valign="top" align="center" height="400"><br>
							<table cellspacing="0" cellpadding="0" width="96%">
								<tr><td class="content_header" align="center"><h3>Konfiguration vom eazySales Connector</h3></td></tr>
								<tr><td class="content" align="center"><br>
										Fr den reibungslosen Im-/ und Export von Daten zwischen <a href="http://www.jtl-software.de">eazySales</a> und Ihrem Shop, mssen einige Einstellungen als Standard gesetzt sein.<br><br>
										<table cellspacing="0" cellpadding="0" width="580">
											<tr>
												<td class="unter_content_header">&nbsp;<b>Einstellungen</b></td>
											</tr>
											<tr>
												<td class="content" align="center">
													Hilfe zu den einzelnen Einstellungm�lichkeiten finden Sie unter <a href="http://www.jtl-software.de/eazySales_connector.php" target="_blank">eazySales Connector Konfigurationshilfe</a>.<br>
													<form action="konfiguration.php" method="post" name="konfig">
													<input type="hidden" name="update" value="1">
													<table cellspacing="0" cellpadding="10" width="100%">
														<tr>
															<td><b>Shop URL</b></td><td><input type="text" name="shopurl" size="50" class="konfig" value="'.$einstellungen->shopURL.'"></td>
														</tr>
														<tr>
															<td><b>Standardw�rung</b></td><td><select name="waehrung">
	');
	$cur_query = xtc_db_query("SELECT * FROM currencies");
	while ($currency = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$currency->currencies_id.'" ');if ($currency->currencies_id==$einstellungen->currencies_id) echo('selected'); echo('>'.$currency->title.'</option>');
	}
	echo('</select></td>
														</tr>
														<tr>
															<td><b>Standardsprache</b></td><td><select name="sprache">
	');
	$cur_query = xtc_db_query("SELECT * FROM languages");
	while ($lang = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$lang->languages_id.'" ');if ($lang->languages_id==$einstellungen->languages_id) echo('selected'); echo('>'.$lang->name.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
														<tr>
															<td><b>Standardliefertermin</b></td><td><select name="liefertermin">
	');
	$cur_query = xtc_db_query("SELECT * FROM shipping_status WHERE language_id=".$einstellungen->languages_id);
	while ($liefer = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$liefer->shipping_status_id.'" ');if ($liefer->shipping_status_id==$einstellungen->shipping_status_id) echo('selected'); echo('>'.$liefer->shipping_status_name.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
														<tr>
															<td>Umsatzsteuer</td><td>&nbsp;</td>
														</tr>
														<tr>
															<td><b>Standard Steuerzone</b></td><td><select name="steuerzone">
	');
	$cur_query = xtc_db_query("SELECT * FROM geo_zones");
	while ($zone = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$zone->geo_zone_id.'" ');if ($zone->geo_zone_id==$einstellungen->tax_zone_id) echo('selected'); echo('>'.$zone->geo_zone_name.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
														<tr>
															<td><b>Standard Steuerklasse*</b></td><td><select name="steuerklasse">
	');
	$cur_query = xtc_db_query("SELECT * FROM tax_class");
	while ($klasse = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$klasse->tax_class_id.'" ');if ($klasse->tax_class_id==$einstellungen->tax_class_id) echo('selected'); echo('>'.$klasse->tax_class_title.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
														<tr>
															<td><b>Standard Steuersatzpriorit�</b></td><td><input type="text" name="prioritaet" size="50" class="konfig" style="width:30px;" value="'.$einstellungen->tax_priority.'"></td>
														</tr>
														<tr>
															<td><b>Steuersatz fr Versandkosten</b></td><td><input type="text" name="versandMwst" size="50" class="konfig" style="width:30px;" value="'.$einstellungen->versandMwst.'"> %</td>
														</tr>
														<tr>
															<td>Bestellstatus�derungen</td><td>&nbsp;</td>
														</tr>
														<tr>
															<td><b>Sobald Bestellung erfolgreich in eazySales bernommen wird, Status setzen auf:</b></td><td><select name="StatusAbgeholt"><option value="0">Status nicht �dern</option>
	');
	$cur_query = xtc_db_query("SELECT * FROM orders_status WHERE language_id=".$einstellungen->languages_id." ORDER BY orders_status_id");
	while ($status = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$status->orders_status_id.'" ');if ($status->orders_status_id==$einstellungen->StatusAbgeholt) echo('selected'); echo('>'.$status->orders_status_name.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
														<tr>
															<td><b>Sobald Bestellung in eazySales versandt wird, Status setzen auf</b></td><td><select name="StatusVersendet"><option value="0">Status nicht �dern</option>
	');
	$cur_query = xtc_db_query("SELECT * FROM orders_status WHERE language_id=".$einstellungen->languages_id." ORDER BY orders_status_id");
	while ($status = mysql_fetch_object($cur_query))
	{
		echo('<option value="'.$status->orders_status_id.'" ');if ($status->orders_status_id==$einstellungen->StatusVersendet) echo('selected'); echo('>'.$status->orders_status_name.'</option>');
	}
	echo('
															</select>														
															</td>
														</tr>
													</table><br>
													eazySales kennt nur die Kundengruppen Endkunde und H�derkunde. Weisen Sie diesen Kundengruppen Ihre Shop-Kundengruppen zu - dies ist fr die korrekte Preiszuordnung unerl�slich. Vergeben Sie nicht Ihre Kundengruppen doppelt.<br>
													<table cellspacing="0" cellpadding="10" width="100%">
														<tr>
															<td valign="top"><b>eazySales Endkunde</b></td><td>
	');
	$cur_query = xtc_db_query("SELECT * FROM customers_status WHERE language_id=".$einstellungen->languages_id." ORDER BY customers_status_id");
	while ($grp = mysql_fetch_object($cur_query))
	{
		echo('<input type="checkbox" name="endkunde[]" value="'.$grp->customers_status_id.'"');if (in_array($grp->customers_status_id,$mappingEndkunde_arr)) echo('checked'); echo('> '.$grp->customers_status_name.'<br>');
	}
															
	echo('
															</td>
														</tr>
														<tr>
															<td valign="top"><b>eazySales H�dlerkunde</b></td><td>
	');
	$cur_query = xtc_db_query("SELECT * FROM customers_status WHERE language_id=".$einstellungen->languages_id." ORDER BY customers_status_id");
	while ($grp = mysql_fetch_object($cur_query))
	{
		echo('<input type="checkbox" name="haendlerkunde[]" value="'.$grp->customers_status_id.'"');if (in_array($grp->customers_status_id,$mappingHaendlerkunde_arr)) echo('checked'); echo('> '.$grp->customers_status_name.'<br>');
	}
															
	echo('
															</td>
														</tr>
													</table><br>
													Vorlagen fr Kategorien und Artikel, die ber eazySales eingestellt werden:
													<table cellspacing="0" cellpadding="10" width="100%">
														<tr>
															<td>Kategorien</td><td>&nbsp;</td>
														</tr>
														<tr>
															<td valign="top"><b>Artikelbersicht in Kategorien</b></td><td><select name="cat_listing">
	');
	if (is_array($product_listing_template_arr))
	{	
		foreach ($product_listing_template_arr as $template)
		{
			echo('<option value="'.$template['id'].'" ');if ($template['id']==$einstellungen->cat_listing_template) echo('selected'); echo('>'.$template['text'].'</option>');
		}
	}
	echo('
															</select>	
															</td>
														</tr>
														<tr>
															<td valign="top"><b>Kategoriebersicht</b></td><td><select name="cat_template">
	');
	if (is_array($category_listing_template_arr))
	{	
		foreach ($category_listing_template_arr as $template)
		{
			echo('<option value="'.$template['id'].'" ');if ($template['id']==$einstellungen->cat_category_template) echo('selected'); echo('>'.$template['text'].'</option>');
		}
	}
	echo('
															</select>	
															</td>
														</tr>
														<tr>
															<td valign="top"><b>Artikelsortierung</b></td><td><select name="cat_sorting">
	');
	if (is_array($order_array))
	{	
		foreach ($order_array as $sortierung)
		{
			echo('<option value="'.$sortierung['id'].'" ');if ($sortierung['id']==$einstellungen->cat_sorting) echo('selected'); echo('>'.$sortierung['text'].'</option>');
		}
	}
	echo('
															</select> <select name="cat_sorting2">
	');
	if (is_array($order_array2))
	{	
		foreach ($order_array2 as $sortierung)
		{
			echo('<option value="'.$sortierung['id'].'" ');if ($sortierung['id']==$einstellungen->cat_sorting2) echo('selected'); echo('>'.$sortierung['text'].'</option>');
		}
	}
	echo('
															</select>
															</td>
														</tr>
														<tr>
															<td>Artikel</td><td>&nbsp;</td>
														</tr>
														<tr>
															<td valign="top"><b>Artikeldetails</b></td><td><select name="product_template">
	');
	if (is_array($productinfo_template_arr))
	{	
		foreach ($productinfo_template_arr as $template)
		{
			echo('<option value="'.$template['id'].'" ');if ($template['id']==$einstellungen->prod_product_template) echo('selected'); echo('>'.$template['text'].'</option>');
		}
	}
	echo('
															</select>	
															</td>
														</tr>
														<tr>
															<td valign="top"><b>Artikeloptionen</b></td><td><select name="option_template">
	');
	if (is_array($productoptions_template_arr))
	{	
		foreach ($productoptions_template_arr as $template)
		{
			echo('<option value="'.$template['id'].'" ');if ($template['id']==$einstellungen->prod_options_template) echo('selected'); echo('>'.$template['text'].'</option>');
		}
	}
	echo('
															</select>	
															</td>
														</tr>
													</table><br>
													<input type="submit" value="Einstellungen speichern">
													</form>
												</td>
											</tr>
										</table><br>
								</td></tr>
							</table><br>
						</td>
	');
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
	xtc_db_query("INSERT INTO eazysales_einstellungen (StatusAbgeholt, StatusVersendet, currencies_id, languages_id, mappingEndkunde, mappingHaendlerkunde, shopURL, tax_class_id, tax_zone_id, tax_priority, shipping_status_id, versandMwst,cat_listing_template,cat_category_template,cat_sorting,cat_sorting2,prod_product_template,prod_options_template) values ($statusAbgeholt, $statusVersandt, $waehrung,$sprache,\"$mappingEndkunde\",\"$mappingHaendlerkunde\",\"$shopurl\",$steuerklasse,$steuerzone,$prioritaet,$liefertermin, ".floatval($versandMwst).",\"$cat_listing\",\"$cat_template\",\"$cat_sorting\",\"$cat_sorting2\",\"$product_template\",\"$option_template\")");
}

function getTemplateArray($cur_template, $module)
{
	$files=array();
	if ($dir= opendir(DIR_FS_CATALOG.'templates/'.$cur_template->configuration_value.'/module/'.$module.'/'))
	{
		while  (($file = readdir($dir)) !==false) 
		{
			if (is_file( DIR_FS_CATALOG.'templates/'.$cur_template->configuration_value.'/module/'.$module.'/'.$file) AND ($file !="index.html"))
			{
				$files[]=array('id' => $file,'text' => $file);
			}
		}
		closedir($dir);
	}	
	return $files;
}
?>

