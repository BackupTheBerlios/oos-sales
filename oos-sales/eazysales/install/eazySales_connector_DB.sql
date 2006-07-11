# $Id: eazySales_connector_DB.sql,v 1.3 2006/07/11 16:13:16 r23 Exp $
#
# wawi - osis online shop
#
# Copyright (c) 2006 by the OOS Development Team.
#
# Based on:
#
# File: eazySales_connector_DB.sql,v 1.00 14.06.06
#
# eazySales_Connector
# http://www.jtl-software.de/eazysales.php
#
# Copyright (c) 2006, JTL-Software
#
# Original Author of file:  JTL-Software <thomas@jtl-software.de>
#
# Released under the GNU General Public License




CREATE TABLE eazysales_mbestellpos (
  kBestellPos int(10) unsigned NOT NULL auto_increment,
  orders_products_id int(10) unsigned default NULL,
  PRIMARY KEY  (kBestellPos)
);

CREATE TABLE eazysales_martikel (
  products_id int(10) unsigned NOT NULL,
  kArtikel int(10) unsigned default NULL,
  PRIMARY KEY  (products_id)
);

CREATE TABLE eazysales_mkategorie (
  categories_id int(10) unsigned NOT NULL,
  kKategorie int(10) unsigned default NULL,
  PRIMARY KEY  (categories_id)
);

CREATE TABLE eazysales_mvariation (
  kEigenschaft int(10) unsigned NOT NULL,
  products_options_id int(10) unsigned default NULL,
  kArtikel int(11) default NULL,
  PRIMARY KEY  (kEigenschaft)
);

CREATE TABLE eazysales_mvariationswert (
  products_attributes_id int(10) unsigned NOT NULL,
  kEigenschaftsWert int(10) unsigned default NULL,
  kArtikel int(11) default NULL,
  PRIMARY KEY  (products_attributes_id)
);

CREATE TABLE eazysales_sentorders (
  orders_id int(10) unsigned NOT NULL,
  dGesendet datetime default NULL,
  PRIMARY KEY  (orders_id)
);

CREATE TABLE eazysales_sync (
  cName varchar(255)  default NULL,
  cPass varchar(255)  default NULL
);
