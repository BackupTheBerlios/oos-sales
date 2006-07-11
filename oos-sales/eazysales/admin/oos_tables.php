<?php
/* ----------------------------------------------------------------------
   $Id: oos_tables.php,v 1.1 2006/07/11 12:09:20 r23 Exp $

   OOS [OSIS Online Shop]
   http://www.oos-shop.de/

   Copyright (c) 2003 - 2006 by the OOS Development Team.
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  $prefix_table = OOS_DB_PREFIX;

  if (!$prefix_table == '') $prefix_table = $prefix_table . '_';

  if (!is_array($oostable)) $oostable = array();

  $oostable['eazysales_adminsession'] = $prefix_table . 'eazysales_adminsession';
  $oostable['eazysales_einstellungen'] = $prefix_table . 'eazysales_einstellungen';
  $oostable['eazysales_mbestellpos'] = $prefix_table . 'eazysales_mbestellpos';
  $oostable['eazysales_martikel'] = $prefix_table . 'eazysales_martikel';
  $oostable['eazysales_mkategorie'] = $prefix_table . 'eazysales_mkategorie';
  $oostable['eazysales_mvariation'] = $prefix_table . 'eazysales_mvariation';
  $oostable['eazysales_mvariationswert'] = $prefix_table . 'eazysales_mvariationswert';
  $oostable['eazysales_sentorders'] = $prefix_table . 'eazysales_sentorders';
  $oostable['eazysales_sync'] = $prefix_table . 'eazysales_sync';

?>
