<?php
/* ----------------------------------------------------------------------
   $Id: getArtikel.php,v 1.9 2006/07/13 03:32:11 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: getArtikel.php,v1.02  03.07.06
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
 * eazySales_Connector/dbeS/getArtikel.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.02 / 03.07.06
*/
require 'syncinclude.php';

$Response="";
$return = 3;
//Auth
if (auth())
{
	$return = 0;
	//hole einstellunegn
        $eazysales_einstellungenstable = $oostable['eazysales_einstellungen'];
	$cur_query = xtc_db_query("SELECT * FROM $eazysales_einstellungenstable");
	$einstellungen = mysql_fetch_object($cur_query);
	
	//get currency
        $currenciestable = $oostable['currencies'];
	$cur_query = xtc_db_query("SELECT *
                                   FROM $currenciestable
                                   WHERE currencies_id=".$einstellungen->currencies_id);
	$currency = mysql_fetch_object($cur_query);
	
	//hole einen noch nicht versandten Artikel nach eS raus 
        $productstable = $oostable['products'];
	$cur_query = xtc_db_query("SELECT products.products_id 
                                   FROM $productstable LEFT JOIN
                                        eazysales_martikel
                                     ON products.products_id=eazysales_martikel.products_id
                                  WHERE eazysales_martikel.products_id is NULL limit 1");
	if ($product_id = mysql_fetch_object($cur_query))
	{
		//hole product
                $productstable = $oostable['products'];
                $products_descriptiontable = $oostable['products_description'];
		$product_query = xtc_db_query("SELECT p.*, pd.*
                                               FROM $productstable p,
                                                    $products_descriptiontable pd
                                               WHERE p.products_id=".$product_id->products_id." 
                                                 AND p.products_id=pd.products_id 
                                                 AND pd.language_id=".$einstellungen->languages_id." 
                                               ORDER BY pd.products_name");
		if ($product = mysql_fetch_object($product_query))
		{	
			//hole VPE
			$vpe="";
			if ($product->products_vpe>0)
			{
				$vpe_query = xtc_db_query("SELECT products_vpe_name 
                                                           FROM products_vpe 
                                                           WHERE language_id=".$GLOBALS['einstellungen']->languages_id."
                                                              AND products_vpe_id=".$product->products_vpe);
				$vpe_res = mysql_fetch_object($vpe_query);
				$vpe = substr($vpe_res->products_vpe_name,0,5);
			}
			//bereite dieses Produkt zum Senden vor
			//hole Steuer
			$tax = get_tax($product->products_tax_class_id);
			//baue Response			
			$Response=CSVkonform("P").";".			
					CSVkonform($product->products_id).";".
					CSVkonform(substr(unhtmlentities($product->products_model),0,20)).";".			
					CSVkonform(substr(unhtmlentities($product->products_name),0,255)).";".			
					CSVkonform(substr(unhtmlentities($product->products_description),0,64000)).";".			
					CSVkonform(substr(unhtmlentities($product->products_short_description),0,254)).";".			
					CSVkonform(get_preisEndkunde($product)+get_preisEndkunde($product)*$currency->value*$tax/100).";".			
					CSVkonform(get_preisEndkunde($product)*$currency->value).";".			
					CSVkonform("").";".			
					CSVkonform($tax).";".			
					CSVkonform("").";".			
					CSVkonform("Y").";".			
					CSVkonform("Y").";".			
					CSVkonform($product->products_quantity).";".			
					CSVkonform($vpe).";".			
					CSVkonform("1").";".			
					CSVkonform(unhtmlentities($product->products_ean)).";".			
					CSVkonform("").";".		
					CSVkonform(get_preisHaendlerKunde($product)+get_preisHaendlerKunde($product)*$currency->value*$tax/100).";".		
					CSVkonform(get_preisHaendlerKunde($product)*$currency->value).";".		
					CSVkonform($product->products_startpage).";".		
					CSVkonform("N").";".		
					CSVkonform("N").";".		
					CSVkonform($product->products_weight).";".		
					CSVkonform("N").";".		
					CSVkonform("N").";".		
					CSVkonform("N").";".		
					CSVkonform("N").";".		
					CSVkonform("0").";".		
					CSVkonform("0").";".		
					CSVkonform(unhtmlentities(getManufacturer($product->manufacturers_id))).";".		
					CSVkonform(get_bildURL($product)).";\n";		
					
			$Response.=get_cats($product->products_id);
			$Response.=get_variationen($product->products_id);
//			$Response.=get_variationswerte($product->products_id);
			$Response.=get_attribute($product);
			$Response.=get_staffelpreise($product->products_id,1);
			$Response.=get_staffelpreise($product->products_id,0);
		}
	}
}
echo($return.";\n");
echo($Response);

function get_bildURL($product)
{
	$pic="";
	//hat produkt bild?
	$imageUrlPrefix="";
	if ($product->products_image)
	{
		$path="";
		if (file_exists(DIR_FS_CATALOG_ORIGINAL_IMAGES.$product->products_image))
			$path=DIR_WS_CATALOG_ORIGINAL_IMAGES;
		elseif (file_exists(DIR_FS_CATALOG_IMAGES.$product->products_image))
			$path=DIR_WS_IMAGES;
			
		if (strlen($path)>1)
		{			
			//is es ein jpg?
			if (eregi("jpg",substr($product->products_image,strlen($product->products_image)-3)))
			{
				$pic=HTTP_CATALOG_SERVER.$path.$product->products_image;
			}
			elseif (eregi("gif",substr($product->products_image,strlen($product->products_image)-3)))
			{
				if(function_exists("ImageCreateFromGIF"))
				{
					$path=DOCROOT_XTC_PATH.$path;
					$im = @ImageCreateFromGIF ($path.$product->products_image);
					if ($im)
					{
						//erstelle dir, falls noch nicht getan
						if (!file_exists(DIR_FS_CATALOG_IMAGES."es_export"))
							mkdir (DIR_FS_CATALOG_IMAGES."es_export");
							
						imagejpeg($im,DIR_FS_CATALOG_IMAGES."es_export/".$product->products_id.".jpg");
						if (file_exists(DIR_FS_CATALOG_IMAGES."es_export/".$product->products_id.".jpg"))
						{
							$pic=$GLOBALS['einstellungen']->shopURL.DIR_FS_CATALOG_IMAGES."es_export/".$product->products_id.".jpg";
						}
					}
				}
			}
		}		
	}
	return $pic;
}

function get_attribute($product)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$attribute="";
	//Lieferstatus
	if ($product->products_shippingtime>0)
	{
		//hole bezeichnung
		$cur_query = xtc_db_query("SELECT shipping_status_name 
                                           FROM shipping_status 
                                           WHERE language_id=".$GLOBALS['einstellungen']->languages_id." 
                                             AND shipping_status_id=".$product->products_shippingtime);
		$status = mysql_fetch_object($cur_query);
		if (strlen($status->shipping_status_name)>0)
		{
			//Attribut hinzufgen
			$attribute.=CSVkonform("T").";".			
				CSVkonform("Lieferstatus").";".
				CSVkonform($status->shipping_status_name).";\n";			
		}
	}
	//Herstellerlink gesetzt?
	if (strlen($product->products_url)>0)
	{
		//Attribut hinzufgen
		$attribute.=CSVkonform("T").";".			
			CSVkonform("Herstellerlink").";".
			CSVkonform($product->products_url).";\n";	
	}

	//ist vpe gesetzt?
	if ($product->products_vpe_value>0)
	{
		//Attribut hinzufgen
		$attribute.=CSVkonform("T").";".			
			CSVkonform("VPE Wert").";".
			CSVkonform($product->products_vpe_value).";\n";
	}
	
	//VPE Status
	if ($product->products_vpe_status>0)
	{
		$attribute.=CSVkonform("T").";".			
			CSVkonform("VPE anzeigen").";".
			CSVkonform("ja").";\n";
	}
	else 
	{
		$attribute.=CSVkonform("T").";".			
			CSVkonform("VPE anzeigen").";".
			CSVkonform("nein").";\n";
	}
		
	//FSK 18?
	if ($product->products_fsk18>0)
	{
		//Attribut hinzufgen
		$attribute.=CSVkonform("T").";".			
			CSVkonform("FSK 18").";".
			CSVkonform("ja").";\n";
	}
	
	//Reihung
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Reihung").";".
		CSVkonform($product->products_sort).";\n";
		
	//Reihungstartseite
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Reihung Startseite").";".
		CSVkonform($product->products_startpage_sort).";\n";
		
	//suchbegriffe
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Suchbegriffe").";".
		CSVkonform($product->products_keywords).";\n";
				
	//meta title
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Meta Title").";".
		CSVkonform($product->products_meta_title).";\n";
				
	//meta description
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Meta Description").";".
		CSVkonform($product->products_meta_description).";\n";
				
	//meta keywords
	$attribute.=CSVkonform("T").";".			
		CSVkonform("Meta Keywords").";".
		CSVkonform($product->products_meta_keywords).";\n";
		
	return $attribute;
}

function get_variationswerte($products_id,$options_id)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$variationswerte="";
	//existieren Variationen fr diesen Artikel?
        $products_attributestable = $oostable['products_attributes'];
	$cur_query = xtc_db_query("SELECT *
                                   FROM $products_attributestable
                                   WHERE options_id=".$options_id."
                                     AND products_id=".$products_id);
	while ($variation = mysql_fetch_object($cur_query))
	{
		if ($variation->options_values_id>0)
		{
			//hole Variationswertnamen etc.
                        $products_options_valuestable = $oostable['products_options_values'];
			$opt_query = xtc_db_query("SELECT products_options_values_name, products_options_values_id
                                                   FROM $products_options_valuestable
                                                   WHERE language_id=".$GLOBALS['einstellungen']->languages_id."
                                                     AND products_options_values_id=".$variation->options_values_id);
			$var_name = mysql_fetch_object($opt_query);
			if ($var_name->products_options_values_id>0)
			{
				if ($variation->price_prefix=="-")
					$variation->options_values_price*=-1;
				$variationswerte.=CSVkonform("W").";".			
					CSVkonform($variation->options_id).";".
					CSVkonform($variation->products_attributes_id).";".
					CSVkonform($var_name->products_options_values_name).";".
					CSVkonform($variation->options_values_price+$variation->options_values_price*$GLOBALS['currency']->value*$GLOBALS['tax']/100).";".
					CSVkonform($variation->options_values_price).";\n";
			}
		}		
	}
	return $variationswerte;
}

function get_variationen($products_id)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$variationen="";
	//existieren Variationen zu diesem Artikel?
        $products_attributestable = $oostable['products_attributes'];
	$cur_query = xtc_db_query("SELECT *
                                   FROM $products_attributestable
                                   WHERE products_id=".$products_id."
                                   GROUP BY options_id");
	while ($variation = mysql_fetch_object($cur_query))
	{
		if ($variation->options_id>0)
		{
			//hole Variationsname etc.
                        $products_optionstable = $oostable['products_options'];
			$opt_query = xtc_db_query("SELECT products_options_name, products_options_id 
                                                   FROM $products_optionstable
                                                   WHERE language_id=".$GLOBALS['einstellungen']->languages_id." 
                                                     AND products_options_id=".$variation->options_id);
			$var_name = mysql_fetch_object($opt_query);
			if ($var_name->products_options_id>0)
			{
				$variationen.=CSVkonform("V").";".			
					CSVkonform($variation->options_id).";".
					CSVkonform($var_name->products_options_name).";".					
					CSVkonform("Y").";\n";
			}
			$variationen.=get_variationswerte($products_id,$variation->options_id);
		}
	}
	return $variationen;
}

function get_staffelpreise($products_id, $endkunde)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$staffel="";
	$anzahl = "";
	$preise = "";
	$personalOfferTable = "personal_offers_by_customers_status_";
	$endKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingEndkunde);
	$haendlerKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingHaendlerkunde);	
	
	$id=-1;
	$staffelpreiseDa=false;
	if ($endkunde==1)
	{
		if (strlen($GLOBALS['einstellungen']->mappingEndkunde)>0)
		{
			$staffelpreiseDa=true;
			$table = $personalOfferTable.$endKunden_arr[0];
		}
	}
	else
	{
		if (strlen($GLOBALS['einstellungen']->mappingHaendlerkunde)>0)
		{
			$staffelpreiseDa=true;
			$table = $personalOfferTable.$haendlerKunden_arr[0];
		}
	}
	$anzahlStaffelpreise=0;
	
	if ($staffelpreiseDa)
	{
		//existieren Staffelprese fr diesen Artikel?
           $productstable = $oostable['products'];
		$cur_query = xtc_db_query("SELECT * 
                                           FROM $productstable 
                                           WHERE products_id=".$products_id." 
                                             AND quantity>1 
                                           ORDER BY quantity asc limit 5");
		while ($staffelpreise = mysql_fetch_object($cur_query))
		{
			$anzahlStaffelpreise++;
			$anzahl.=";".CSVkonform($staffelpreise->quantity);
			$preise.=";".CSVkonform($staffelpreise->personal_offer*$GLOBALS['currency']->value);
		}
		if (strlen($anzahl)>0)
		{
			$staffel=CSVkonform("SH");
			if ($endkunde)
				$staffel=CSVkonform("SP");
			$staffel.=$anzahl;
			for ($i=0;$i<5-$anzahlStaffelpreise;$i++)
				$staffel.=";".CSVkonform("0");	
			$staffel.=$preise;
			for ($i=0;$i<5-$anzahlStaffelpreise;$i++)
				$staffel.=";".CSVkonform("0");	
			$staffel.=";\n";	
		}
	}
	return $staffel;
}

function get_preisEndkunde($product)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$endKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingEndkunde);
	if ($endKunden_arr[0]>0)
	{
		$personalOfferTable = "personal_offers_by_customers_status_".$endKunden_arr[0];
		$cur_query = xtc_db_query("SELECT * FROM $personalOfferTable WHERE quantity=1 AND products_id=".$product->products_id);
		$staffelpreise = mysql_fetch_object($cur_query);
		if ($staffelpreise->quantity == 1 && $staffelpreise->personal_offer>0)
			return $staffelpreise->personal_offer;
	}
	return $product->products_price;
}

function get_preisHaendlerKunde($product)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$haendlerKunden_arr = explode(";",$GLOBALS['einstellungen']->mappingHaendlerkunde);
	if ($haendlerKunden_arr[0]>0)
	{
		$personalOfferTable = "personal_offers_by_customers_status_".$haendlerKunden_arr[0];
		$cur_query = xtc_db_query("SELECT * FROM $personalOfferTable WHERE quantity=1 AND products_id=".$product->products_id);
		$staffelpreise = mysql_fetch_object($cur_query);
		if ($staffelpreise->quantity == 1 && $staffelpreise->personal_offer>0)
			return $staffelpreise->personal_offer;
	}
	return 0;
}

//get categroies 
function get_cats($products_id)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

	$res ="";
	//get cat_id

        $products_to_categoriestable = $oostable['products_to_categories'];
	$glob_cat_query = xtc_db_query("SELECT * 
                                        FROM $products_to_categoriestable
                                        WHERE products_id=".$products_id);
	while ($act_cat = mysql_fetch_object($glob_cat_query))
	{
		$catArr = array();
		$cur_cat_id = $act_cat->categories_id;
		if ($cur_cat_id>0)
		{

                  $categoriestable = $oostable['categories'];
			$cat_query = xtc_db_query("SELECT *
                                                   FROM $categoriestable
                                                   WHERE categories_id=".$cur_cat_id);
			$current_cat = mysql_fetch_object($cat_query);	
			array_push($catArr,$cur_cat_id);
		}
		
		while ($current_cat->parent_id>0)
		{
			$cur_cat_id = $current_cat->parent_id;

                  $categoriestable = $oostable['categories'];
			$cat_query = xtc_db_query("SELECT *
                                                    FROM $categoriestable
                                                    WHERE categories_id=".$cur_cat_id);
			$current_cat = mysql_fetch_object($cat_query);
			array_push($catArr,$cur_cat_id);
		}
		$cnt = count($catArr);
		for ($i=0;$i<$cnt;$i++)
		{
			$vor="UK";
			if ($i==0)
				$vor="K";
			$catId = array_pop($catArr);

                  $categories_descriptiontable = $oostable['categories_description'];
			$cat_query = xtc_db_query("SELECT *
                                                   FROM $categories_descriptiontable
                                                   WHERE categories_id=".$catId."
                                                     AND language_id=".$GLOBALS['einstellungen']->languages_id);
			$current_cat = mysql_fetch_object($cat_query);

                  $categoriestable = $oostable['categories'];
			$cat_query = xtc_db_query("SELECT categories_status
                                                   FROM $categoriestable
                                                   WHERE categories_id=".$catId);
			$current_cat_status = mysql_fetch_object($cat_query);

			$res.=CSVkonform($vor).";"
				CSVkonform(unhtmlentities($current_cat->categories_name)).";".
				CSVkonform(substr(unhtmlentities($current_cat->categories_description),0,64000)).";".
				CSVkonform($catId).";"
				CSVkonform($current_cat_status->categories_status).";\n";
		}
	}
	if ($res=="")
		$res.=CSVkonform("K").";"
			CSVkonform("Top").";"
			CSVkonform("Wurzelkategorie").";"
			CSVkonform(0).";".
			CSVkonform(0).";\n";
	return $res;
}

//get hersteller
function getManufacturer($manufacturers_id)
{
    // Get database information
    $dbconn =& oosDBGetConn();
    $oostable =& oosDBGetTables();

    $manufacturerstable = $oostable['manufacturers'];
	$manu_query = xtc_db_query("SELECT *
                                    FROM $manufacturerstable
                                    WHERE manufacturers_id=".$manufacturers_id);
	$manu = mysql_fetch_object($manu_query);
	return ($manu->manufacturers_name);
}

?>