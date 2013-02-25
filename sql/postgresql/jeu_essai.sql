

/*---------------------------------------------------------------------------
 CATALOGUES
----------------------------------------------------------------------------*/

INSERT INTO CATALOG_ENTRY VALUES(1, 'GENERIC'/*, 'Alimentaire'*/);
INSERT INTO CATALOG_ENTRY VALUES(2, 'GENERIC'/*, 'Véhicules'*/);

/*---------------------------------------------------------------------------
 CATEGORIES
----------------------------------------------------------------------------*/

INSERT INTO CATALOG_CATEGORY VALUES('PRODUCT', 'Produit', 'PRODUCT');
INSERT INTO CATALOG_CATEGORY VALUES('VEGETABLE', 'Légume', 'VEGETABLE');
INSERT INTO CATALOG_CATEGORY VALUES('VEHICLE', 'Véhicule', 'VEHICLE');

/*---------------------------------------------------------------------------
 ITEMS
----------------------------------------------------------------------------*/

INSERT INTO CATALOG_ITEM VALUES(1, 1, 'Citroën C3 Picasso', 'Véhicule monospace compact familliale. Design et caractère !', '2013/05/01');
INSERT INTO CATALOG_ITEM VALUES(2, 1, 'Carottes des maldives', 'Les meilleurs carottes du monde !', '2013/05/01');

/*---------------------------------------------------------------------------
 Assocation ITEM/CATEGORIES
----------------------------------------------------------------------------*/

/* Citroën C3 Picasso */
INSERT INTO ASSOCIER VALUES(1, 'PRODUCT');
INSERT INTO ASSOCIER VALUES(1, 'VEHICULE');
INSERT INTO PRODUCT VALUES(1, 1, '23000.00', 'EUR', 'Unit', 12543);
INSERT INTO VEHICLE VALUES(1, 1, 'Monospace-compact', 5, 5, 6.0);

/* Carottes des maldives */
INSERT INTO CATALOG_ITEM_CATEGORY VALUES(2, 'PRODUCT');
INSERT INTO CATALOG_ITEM_CATEGORY VALUES(2, 'VEGETABLE');
INSERT INTO PRODUCT VALUES(2, 2, '5,00', 'EUR', 'Kg', 125000);
INSERT INTO VEGETABLE VALUES(2, 2, 'Ombellifères');
