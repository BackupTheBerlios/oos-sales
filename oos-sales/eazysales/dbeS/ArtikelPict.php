<?php
/* ----------------------------------------------------------------------
   $Id: ArtikelPict.php,v 1.13 2006/09/26 00:51:20 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: ArtikelPict.php,v1.0  16.06.06
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
 * eazySales_Connector/dbeS/ArtikelPict.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/

  define('OOS_VALID_MOD', 'yes');

  require 'syncinclude.php';

$picpath = "../produktbilder/";
  $return = 3;

  if (auth()) {

	$return = 0;
/*	$ArtikelPict = new ArtikelPict();
	if (intval($_POST['action']) == 1 && $ArtikelPict->setzePostDaten())
	{
		$oldArtikelPict = new ArtikelPict();
		$oldArtikelPict->loadFromDB($ArtikelPict->kArtikel);

		$GLOBALS["DB"]->executeQuery("DELETE FROM tartikelpict WHERE kArtikel=".$ArtikelPict->kArtikel,4);

		if ($ArtikelPict->insertInDB())
			$return = 0;
		else
			$return = 1;

		//gibt es alte Bilder zum l�chen?
		if ($oldArtikelPict->cPfad1 && !$ArtikelPict->cPfad1)
		{
			//bild nr 1 existiert nicht mehr. im fs l�chen
			if (file_exists($picpath.$oldArtikelPict->cPfad1))
			{
				unlink($picpath.$oldArtikelPict->cPfad1);
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad1, 0, -4).'-m.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad1, 0, -4).'-m.jpg');
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad1, 0, -4).'-s.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad1, 0, -4).'-s.jpg');
			}
		}
		if ($oldArtikelPict->cPfad2 && !$ArtikelPict->cPfad2)
		{
			//bild nr 2 existiert nicht mehr. im fs l�chen
			if (file_exists($picpath.$oldArtikelPict->cPfad2))
			{
				unlink($picpath.$oldArtikelPict->cPfad2);
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad2, 0, -4).'-m.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad2, 0, -4).'-m.jpg');
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad2, 0, -4).'-s.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad2, 0, -4).'-s.jpg');
			}
		}
		if ($oldArtikelPict->cPfad3 && !$ArtikelPict->cPfad3)
		{
			//bild nr 3 existiert nicht mehr. im fs l�chen
			if (file_exists($picpath.$oldArtikelPict->cPfad3))
			{
				unlink($picpath.$oldArtikelPict->cPfad3);
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad3, 0, -4).'-m.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad3, 0, -4).'-m.jpg');
				if (file_exists(substr($picpath.$oldArtikelPict->cPfad3, 0, -4).'-s.jpg'))
					unlink(substr($picpath.$oldArtikelPict->cPfad3, 0, -4).'-s.jpg');
			}
		}

		//bilder skalieren
		if($ArtikelPict->cPfad1 && file_exists($picpath . $ArtikelPict->cPfad1))
		{
			$picbig = $picpath . $ArtikelPict->cPfad1;
			$picmedium = substr($picpath . $ArtikelPict->cPfad1, 0, -4).'-m.jpg';
			$picsmall = substr($picpath . $ArtikelPict->cPfad1, 0, -4).'-s.jpg';
			$image = imagecreatefromjpeg($picbig);
			list($width, $height) = getimagesize($picbig);
			$ratio = $width / $height;

			//thumbnail
			$new_width = 80;
			$new_height = round (80 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picsmall, 80);

			//medium
			$new_width = 210;
			$new_height = round (210 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picmedium, 80);

			//gro�			$new_width = 800;
			$new_height = round (800 / $ratio);
			if ($width>$new_width || $height>$new_height)
			{
				$image_p = imagecreatetruecolor($new_width, $new_height);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_p, $picbig, 100);
			}
		}

		if( $ArtikelPict->cPfad2 && file_exists($picpath . $ArtikelPict->cPfad2))
		{
			$picbig = $picpath . $ArtikelPict->cPfad2;
			$picmedium = substr($picpath . $ArtikelPict->cPfad2, 0, -4).'-m.jpg';
			$picsmall = substr($picpath . $ArtikelPict->cPfad2, 0, -4).'-s.jpg';
			$image = imagecreatefromjpeg($picbig);
			list($width, $height) = getimagesize($picbig);
			$ratio = $width / $height;

			//thumbnail
			$new_width = 80;
			$new_height = round (80 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picsmall, 100);

			//medium
			$new_width = 210;
			$new_height = round (210 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picmedium, 100);

			//medium
			$new_width = 800;
			$new_height = round (800 / $ratio);
			if ($width>$new_width || $height>$new_height)
			{
				$image_p = imagecreatetruecolor($new_width, $new_height);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_p, $picbig, 100);
			}
		}

		if( $ArtikelPict->cPfad3 && file_exists($picpath . $ArtikelPict->cPfad3))
		{
			$picbig = $picpath . $ArtikelPict->cPfad3;
			$picmedium = substr($picpath . $ArtikelPict->cPfad3, 0, -4).'-m.jpg';
			$picsmall = substr($picpath . $ArtikelPict->cPfad3, 0, -4).'-s.jpg';
			$image = imagecreatefromjpeg($picbig);
			list($width, $height) = getimagesize($picbig);
			$ratio = $width / $height;

			//thumbnail
			$new_width = 80;
			$new_height = round (80 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picsmall, 100);

			//medium
			$new_width = 210;
			$new_height = round (210 / $ratio);
			$image_p = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $picmedium, 100);

			//medium
			$new_width = 800;
			$new_height = round (800 / $ratio);
			if ($width>$new_width || $height>$new_height)
			{
				$image_p = imagecreatetruecolor($new_width, $new_height);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($image_p, $picbig, 100);
			}
		}
 	}
	else
		$return=5;
	*/
	if (intval($_POST['action']) == 3 && intval($_POST['KeyArtikel'])>0)
	{
		$return = 0;
		//hol products_id
		$products_id = getFremdArtikel(intval($_POST['KeyArtikel']));

          $productstable = $oostable['products'];
		xtc_db_query("UPDATE $productstable SET products_image='' WHERE products_id=".$products_id);
	}
	
}


echo($return);
//logge($return);
?>