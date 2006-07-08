<?php
/**
 * eazySales_Connector/dbeS/GetBestellungPos.php
 * Synchronisationsscript
 * 
 * Es gelten die Nutzungs- und Lizenzhinweise unter http://www.jtl-software.de/eazysales.php
 * 
 * @author JTL-Software <thomas@jtl-software.de>
 * @copyright 2006, JTL-Software
 * @link http://jtl-software.de/eazysales.php
 * @version v1.0 / 15.06.06
*/

require_once("syncinclude.php");

$return=3;
if (auth())
{
	$return=5;
	if (intval($_POST['KeyBestellung']))
	{
		$return = 0;		
		//hole orderposes
		$cur_query = eS_execute_query("select * from orders_products where orders_id=".intval($_POST['KeyBestellung'])." order by orders_products_id");
		while ($BestellungPos = mysql_fetch_object($cur_query))
		{
			//hole etl aufpreise
			$aufpreis=0;
			$aufpreise_query = eS_execute_query("select options_values_price from orders_products_attributes where orders_id=".$BestellungPos->orders_id." and orders_products_id=".$BestellungPos->orders_products_id." and options_values_price!=0");
			while ($aufpreis_arr = mysql_fetch_row($aufpreise_query))
			{
				$aufpreis+=($aufpreis_arr[0]*(100+$BestellungPos->products_tax))/100;
			}
			//mappe bestellpos
			$kBestellPos = setMappingBestellPos($BestellungPos->orders_products_id);
			echo(CSVkonform($kBestellPos).';');
			echo(CSVkonform(intval($_POST['KeyBestellung'])).';');
			echo(CSVkonform(getEsArtikel($BestellungPos->products_id)).';');
			echo(CSVkonform($BestellungPos->products_name).';');
			echo(CSVkonform($BestellungPos->products_price-$aufpreis).';');
			echo(CSVkonform($BestellungPos->products_tax).';');
			echo(CSVkonform($BestellungPos->products_quantity).';');
			echo("\n");
		}
		//letzte Position Versand
		$cur_query = eS_execute_query("select * from orders_total where class=\"ot_shipping\" and orders_id=".intval($_POST['KeyBestellung']));
		if ($Versand = mysql_fetch_object($cur_query))
		{
			//mappe bestellpos
			$kBestellPos = setMappingBestellPos(0);

			//hole versand mwst aus einstellungen 
			$cur_query = eS_execute_query("select versandMwst from eazysales_einstellungen");
			$einstellungen = mysql_fetch_object($cur_query);
			
			echo(CSVkonform($kBestellPos).';');
			echo(CSVkonform(intval($_POST['KeyBestellung'])).';');
			echo(CSVkonform("0").';');
			echo(CSVkonform($Versand->title).';');
			echo(CSVkonform($Versand->value).';');
			echo(CSVkonform($einstellungen->versandMwst).';');
			echo(CSVkonform("1").';');
			echo("\n");
		}
	}
}
mysql_close();
echo($return);
logge($return);
?>