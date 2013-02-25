/*==============================================================*/
/* Nom de SGBD :  PostgreSQL 8 (WFW)                            */
/* Date de création :  25/02/2013 10:18:16                      */
/*==============================================================*/


drop table if exists CATALOG_CATEGORY  CASCADE;

drop table if exists CATALOG_ENTRY  CASCADE;

drop table if exists CATALOG_ITEM  CASCADE;

drop domain if exists CATALOG_ITEM_TYPE CASCADE;

drop domain if exists CATALOG_TYPE CASCADE;

/*==============================================================*/
/* Domaine : CATALOG_ITEM_TYPE                                  */
/*==============================================================*/
create domain CATALOG_ITEM_TYPE as VARCHAR(10);

comment on domain CATALOG_ITEM_TYPE is
'Ajouter ici les types d''items héritant de l''entité CATALOG_ITEM';

/*==============================================================*/
/* Domaine : CATALOG_TYPE                                       */
/*==============================================================*/
create domain CATALOG_TYPE as VARCHAR(10);

comment on domain CATALOG_TYPE is
'Ajoutez ici les types de catalogues héritant de l''entité CATALOG';

/*==============================================================*/
/* Table : CATALOG_CATEGORY                                     */
/*==============================================================*/
create table CATALOG_CATEGORY (
   CATALOG_CATEGORY_ID  VARCHAR(80)          not null,
   CATEGORY_DESC        VARCHAR(256)         not null,
   constraint PK_CATALOG_CATEGORY primary key (CATALOG_CATEGORY_ID)
);

/*==============================================================*/
/* Table : CATALOG_ENTRY                                        */
/*==============================================================*/
create table CATALOG_ENTRY (
   CATALOG_ENTRY_ID     INT4                 not null,
   CATALOG_TYPE         CATALOG_TYPE         not null,
   constraint PK_CATALOG_ENTRY primary key (CATALOG_ENTRY_ID)
);

/*==============================================================*/
/* Table : CATALOG_ITEM                                         */
/*==============================================================*/
create table CATALOG_ITEM (
   CATALOG_ITEM_ID      INT4                 not null,
   CATALOG_ENTRY_ID     INT4                 not null,
   CATALOG_CATEGORY_ID  VARCHAR(80)          null,
   ITEM_TITLE           VARCHAR(80)          not null,
   ITEM_DESC            VARCHAR(256)         not null,
   ITEM_TYPE            CATALOG_ITEM_TYPE    not null,
   constraint PK_CATALOG_ITEM primary key (CATALOG_ITEM_ID)
);

alter table CATALOG_ITEM
   add constraint FK_CATALOG__ASSOCIATI_CATALOG_ foreign key (CATALOG_ENTRY_ID)
      references CATALOG_ENTRY (CATALOG_ENTRY_ID)
      on delete restrict on update restrict;

alter table CATALOG_ITEM
   add constraint FK_CATALOG__ASSOCIER_CATALOG_ foreign key (CATALOG_CATEGORY_ID)
      references CATALOG_CATEGORY (CATALOG_CATEGORY_ID)
      on delete restrict on update restrict;

