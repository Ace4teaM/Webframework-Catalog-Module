/*
  (C)2013 Thomas AUGUEY
  PL/pgSQL
  Module Catalogue
  
  Initialise les objets et le contenu de base avant utilisation
*/


/*
--------------------------------------------------------------------------
     Defauts
--------------------------------------------------------------------------
*/

-- définit la date en cours aux avis
ALTER TABLE catalog_item ALTER COLUMN creation_date SET DEFAULT CURRENT_TIMESTAMP;
