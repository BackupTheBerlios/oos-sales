<?php
/* ----------------------------------------------------------------------
   $Id: getCountArtikel.php,v 1.5 2006/07/09 02:20:22 r23 Exp $

   wawi - osis online shop

   Copyright (c) 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: getCountArtikel.php,v1.0  15.06.06
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
 * eazySales_Connector/dbeS/getCountArtikel.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 15.06.06
*/
require 'syncinclude.php';
$return=3;
$anzahlArt=0;
//Auth
if (auth())
{
	$return=0;	
	//hole anzahl zu versendender Artikel
	$cur_query = xtc_db_query("SELECT count(*) FROM products LEFT JOIN eazysales_martikel ON products.products_id=eazysales_martikel.products_id WHERE eazysales_martikel.products_id is NULL");
	if ($anzahl = mysql_fetch_row($cur_query))
	{
		if ($anzahl>0)
		{
			$anzahlArt = $anzahl[0];
		}
	}
}
mysql_close();
echo($return.";".$anzahlArt);
//logge($return);
?>
