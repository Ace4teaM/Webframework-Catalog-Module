/*
  (C)2013 AUGUEY Thomas
  PL/pgSQL
  Module Catalogue (WFW_CATALOG)
  
  PostgreSQL v8.3 (version minimum requise)
*/

/*
  Recherche des items
  @param p_text         Optionel, Texte à rechercher
  @param p_catalog      Identifiant du catalogue
  @param p_category     Optionel, Catégorie de l'item
  @param p_type         Optionel, Type d'item
  @param p_sort         Optionel, Nom de la colonne de tri (order by)
*/
create or replace function catalog_search_items( 
	p_text varchar,
	p_catalog catalog_entry.catalog_entry_id%type,
	p_category catalog_category.catalog_category_id%type,
	p_type catalog_category.item_type%type,
	p_sort varchar
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
             ' inner join catalog_category c on c.catalog_category_id in (select a.catalog_category_id from catalog_associer a where a.catalog_item_id = i.catalog_item_id)';

    -- Catalogue
    if p_catalog is not null then 
            cond := cond || ' and ( catalog_entry_id = ' || p_catalog || ')';
    end if;

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

    -- Type
    if p_sort is not null then 
            query := query || ' order by ' || p_sort;
    end if;

    RAISE NOTICE '%',query;

    --execute la requete (ajoute les entrees au resultat)
    for ret in execute query
    loop
            return next ret;
    end loop;

    return;
end;
$$
LANGUAGE plpgsql;



/*
  Recherche les types associés à un item
*/
create or replace function catalog_items_types( 
	p_item_id catalog_item.catalog_item_id%type
)
returns SETOF catalog_category.item_type%type
as $$
begin
    return query select distinct c.item_type as item_type from catalog_category c
            inner join catalog_associer a on a.catalog_category_id = c.catalog_category_id and a.catalog_item_id = p_item_id;
end;
$$
LANGUAGE plpgsql;


/*
  Recherche les catégories associés à un item
*/
create or replace function catalog_items_category( 
	p_item_id catalog_item.catalog_item_id%type
)
returns SETOF catalog_category
as $$
begin
    return query select distinct c.* from catalog_category c
        inner join catalog_item i on i.catalog_item_id = p_item_id
        inner join catalog_associer a on a.catalog_item_id = i.catalog_item_id
        where c.catalog_category_id = a.catalog_category_id;
end;
$$
LANGUAGE plpgsql;


/*
  Recherche des catalogues
  @param p_type         Optionel, Type de catalogue
  @param p_sort         Optionel, Nom de la colonne de tri (order by)
*/
create or replace function catalog_search_entry( 
	p_type catalog_entry.catalog_type%type,
	p_sort varchar
)
returns SETOF catalog_entry
as $$
declare
    ret catalog_entry%rowtype;
    query varchar;
    find_text varchar;
    cond varchar default '';
begin
    -- Requete
    query := 'select distinct i.* from catalog_entry i';

    -- Type
    if p_type is not null then 
        find_text := quote_literal(lower(p_type));
        cond := cond || ' and (lower(i.catalog_type) = ' || find_text || ')';
    end if;

    -- Ajoute la condition
    query := query || ' where 1=1' || cond;

    -- Type
    if p_sort is not null then 
        query := query || ' order by ' || p_sort;
    end if;

    RAISE NOTICE '%',query;

    --execute la requete (ajoute les entrees au resultat)
    for ret in execute query
    loop
            return next ret;
    end loop;

    return;
end;
$$
LANGUAGE plpgsql;
