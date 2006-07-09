<?php
/* ----------------------------------------------------------------------
   $Id: setKategorieBild.php,v 1.5 2006/07/09 02:20:22 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: setKategorieBild.php,v 1.0 20.06.06
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
 * @version v1.0 / 20.06.06
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
		//hol categories_id
		$categories_id = getFremdKategorie(intval($_POST['kArtikelBild']));
		$bildname=$categories_id.".jpg";
		move_uploaded_file($_FILES['bild']['tmp_name'],DIR_FS_CATALOG_IMAGES."categories/".$bildname);
		//updaten
		xtc_db_query("update categories set categories_image=\"$bildname\" WHERE categories_id=".$categories_id);
	}
}
mysql_close();
echo($return);
logge($return);

?>