<?php
/* ----------------------------------------------------------------------
   $Id: KategorieArtikel.php,v 1.3 2006/07/09 02:00:18 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: KategorieArtikel.php,v1.01  16.06.06
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
 * eazySales_Connector/dbeS/KategorieArtikel.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 16.06.06
*/
require 'syncinclude.php';

$return=3;
if (auth())
{
	if (intval($_POST["action"]) == 1 && intval($_POST['KeyKategorieArtikel']))
	{
		$return = 0;
		$KategorieArtikel->kArtikel = intval($_POST["KeyArtikel"]);
		$KategorieArtikel->kKategorie = intval($_POST["KeyKategorie"]);
		$products_id = getFremdArtikel($KategorieArtikel->kArtikel);
		$categories_id = getFremdKategorie($KategorieArtikel->kKategorie);
		if ($products_id && $categories_id)
			eS_execute_query("insert into products_to_categories (products_id, categories_id) values ($products_id, $categories_id)");
 	}
	else
		$return=5;

	if (intval($_POST["action"]) == 3 && intval($_POST['KeyKategorieArtikel']))
	{
		$return = 0;
	}
}

mysql_close();
echo($return);
logge($return);
?>