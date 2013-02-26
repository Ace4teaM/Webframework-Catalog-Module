/*==============================================================*/
/* Nom de SGBD :  PostgreSQL 8 (WFW)                            */
/* Date de création :  26/02/2013 11:43:48                      */
/*==============================================================*/


drop table if exists ASSOCIER  CASCADE;

drop table if exists CATALOG_CATEGORY  CASCADE;

drop table if exists CATALOG_ENTRY  CASCADE;

drop table if exists CATALOG_ITEM  CASCADE;

drop table if exists PRODUCT  CASCADE;

drop table if exists VEGETABLE  CASCADE;

drop table if exists VEHICLE  CASCADE;

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
/* Table : ASSOCIER                                             */
/*==============================================================*/
create table ASSOCIER (
   CATALOG_ITEM_ID      INT4                 not null,
   CATALOG_CATEGORY_ID  VARCHAR(80)          not null,
   constraint PK_ASSOCIER primary key (CATALOG_ITEM_ID, CATALOG_CATEGORY_ID)
);

/*==============================================================*/
/* Table : CATALOG_CATEGORY                                     */
/*==============================================================*/
create table CATALOG_CATEGORY (
   CATALOG_CATEGORY_ID  VARCHAR(80)          not null,
   CATEGORY_DESC        VARCHAR(256)         not null,
   ITEM_TYPE            CATALOG_ITEM_TYPE    not null,
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
   ITEM_TITLE           VARCHAR(80)          not null,
   ITEM_DESC            VARCHAR(256)         not null,
   CREATION_DATE        TIMESTAMP            not null,
   constraint PK_CATALOG_ITEM primary key (CATALOG_ITEM_ID)
);

/*==============================================================*/
/* Table : PRODUCT                                              */
/*==============================================================*/
create table PRODUCT (
   CATALOG_ITEM_ID      INT4                 not null,
   PRODUCT_ID           INT4                 not null,
   PRICE                FLOAT8               not null,
   MONEY                VARCHAR(3)           not null,
   UNIT                 VARCHAR(16)          not null,
   QUANTITY             INT4                 null,
   constraint PK_PRODUCT primary key (CATALOG_ITEM_ID, PRODUCT_ID)
);

/*==============================================================*/
/* Table : VEGETABLE                                            */
/*==============================================================*/
create table VEGETABLE (
   CATALOG_ITEM_ID      INT4                 not null,
   VEGETABLE_ID         INT4                 not null,
   FAMILLE              VARCHAR(120)         not null,
   constraint PK_VEGETABLE primary key (CATALOG_ITEM_ID, VEGETABLE_ID)
);

/*==============================================================*/
/* Table : VEHICLE                                              */
/*==============================================================*/
create table VEHICLE (
   CATALOG_ITEM_ID      INT4                 not null,
   VEHICLE_ID           INT4                 not null,
   TYPE                 VARCHAR(50)          not null,
   N_PLACES             INT4                 null,
   N_DOORS              INT4                 null,
   CONSUMPTION          FLOAT8               null,
   constraint PK_VEHICLE primary key (CATALOG_ITEM_ID, VEHICLE_ID)
);

alter table ASSOCIER
   add constraint FK_ASSOCIER_ASSOCIER_CATALOG_ foreign key (CATALOG_CATEGORY_ID)
      references CATALOG_CATEGORY (CATALOG_CATEGORY_ID)
      on delete restrict on update restrict;

alter table ASSOCIER
   add constraint FK_ASSOCIER_ASSOCIER2_CATALOG_ foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table CATALOG_ITEM
   add constraint FK_CATALOG__ASSOCIATI_CATALOG_ foreign key (CATALOG_ENTRY_ID)
      references CATALOG_ENTRY (CATALOG_ENTRY_ID)
      on delete restrict on update restrict;

alter table PRODUCT
   add constraint FK_PRODUCT_HERITAGE__CATALOG_ foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table VEGETABLE
   add constraint FK_VEGETABL_HERITAGE__CATALOG_ foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table VEHICLE
   add constraint FK_VEHICLE_HERITAGE__CATALOG_ foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

