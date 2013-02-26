/*
  (C)2013 AUGUEY Thomas
  PL/pgSQL
  Module Catalogue (WFW_CATALOG)
  
  PostgreSQL v8.3 (version minimum requise)
*/

/*
  Recherche des items
*/
create or replace function catalog_search_items( 
	p_text varchar,
	p_category catalog_category.catalog_category_id%type,
	p_type catalog_category.item_type%type
)
returns SETOF catalog_item
as $$
declare
    ret catalog_item%rowtype;
    query varchar;
    cond varchar default '';
    find_text varchar;
begin
	-- Requete
	query := 'select distinct i.* from catalog_item i' ||
		 ' inner join catalog_category c on c.catalog_category_id in (select a.catalog_category_id from associer a where a.catalog_item_id = i.catalog_item_id)';

	-- Text
	if p_text is not null then 
		find_text := quote_literal('%'||lower(escape_accents(p_text))||'%');
		cond := cond || ' and (lower(escape_accents(i.item_title)) like ' || find_text || ' or lower(escape_accents(i.item_desc)) like ' || find_text || ')';
	end if;
	
	-- Categorie
	if p_category is not null then 
		find_text := quote_literal(lower(p_category));
		cond := cond || ' and (lower(c.catalog_category_id) = ' || find_text || ')';
	end if;
	
	-- Type
	if p_type is not null then 
		find_text := quote_literal(lower(p_type));
		cond := cond || ' and (lower(c.item_type) = ' || find_text || ')';
	end if;
	
	-- Ajoute la condition
	query := query || ' where 1=1' || cond;

	RAISE NOTICE '%',query;

	--execute la requete (chaque entrée est ajoutée au resultat)
	for ret in execute query
	loop
		return next ret;
	end loop;

	return;
end;
$$
LANGUAGE plpgsql;

