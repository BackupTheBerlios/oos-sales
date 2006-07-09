<?php
/* ----------------------------------------------------------------------
   $Id: setArtikelBild.php,v 1.5 2006/07/09 02:20:22 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: setArtikelBild.php,v 1.0 28.06.06
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
 * eazySales_Connector/dbeS/setArtikelBild.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 28.06.06
*/

require 'syncinclude.php';

$return=3;
$_POST['userID'] = $_POST['euser'];
$_POST['userPWD'] = $_POST['epass'];
if (auth())
{
	$return=0;
	//nur BildNr 1 wird bercksichtigt
	if (intval($_POST['kArtikelBild'])>0 && intval($_POST['nNr'])==1 && $_FILES['bild'])
	{
		//hol products_id
		$products_id = getFremdArtikel(intval($_POST['kArtikelBild']));		
		$bildname=$products_id."_".(intval($_POST['nNr'])-1).".jpg";
		move_uploaded_file($_FILES['bild']['tmp_name'],DIR_FS_CATALOG_ORIGINAL_IMAGES.$bildname);
		
		$im = @ImageCreateFromJPEG (DIR_FS_CATALOG_ORIGINAL_IMAGES.$bildname);
		if ($im)
		{	
			//bild skalieren
			list($width, $height) = getimagesize(DIR_FS_CATALOG_ORIGINAL_IMAGES.$bildname);
			$ratio = $width / $height;
			
			//thumbnail
			$cur_query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key=\"PRODUCT_IMAGE_THUMBNAIL_WIDTH\"");
			$width_obj = mysql_fetch_object($cur_query);
			$new_width = 120;
			if ($width_obj->configuration_value>0)
				$new_width = $width_obj->configuration_value;
			$new_height = round ($new_width / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, DIR_FS_CATALOG_THUMBNAIL_IMAGES.$bildname, 80);
			
			//info
			$cur_query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key=\"PRODUCT_IMAGE_INFO_WIDTH\"");
			$width_obj = mysql_fetch_object($cur_query);
			$new_width = 200;
			if ($width_obj->configuration_value>0)
				$new_width = $width_obj->configuration_value;
			$new_height = round ($new_width / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, DIR_FS_CATALOG_INFO_IMAGES.$bildname, 80);
						
			//popup
			$cur_query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key=\"PRODUCT_IMAGE_POPUP_WIDTH\"");
			$width_obj = mysql_fetch_object($cur_query);
			$new_width = 300;
			if ($width_obj->configuration_value>0)
				$new_width = $width_obj->configuration_value;
			$new_height = round ($new_width / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, DIR_FS_CATALOG_POPUP_IMAGES.$bildname, 80);
		
			//updaten
			xtc_db_query("update products set products_image=\"$bildname\" WHERE products_id=".$products_id);

		}
	}
}
mysql_close();
echo($return);
logge($return);

?>