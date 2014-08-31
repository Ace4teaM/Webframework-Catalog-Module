/*==============================================================*/
/* DBMS name:      PostgreSQL 8 (WFW)                           */
/* Created on:     31/08/2014 21:35:58                          */
/*==============================================================*/


drop index  if exists CATALOG_ASSOCIER2_FK;

drop index  if exists CATALOG_ASSOCIER_FK;

drop index  if exists CATALOG_ASSOCIER_PK;

drop table if exists CATALOG_ASSOCIER  CASCADE;

drop index  if exists CATALOG_CATEGORY_PK;

drop table if exists CATALOG_CATEGORY  CASCADE;

drop index  if exists CATALOG_ENTRY_PK;

drop table if exists CATALOG_ENTRY  CASCADE;

drop index  if exists CATALOG_ATTACHER_FK;

drop index  if exists CATALOG_ITEM_PK;

drop table if exists CATALOG_ITEM  CASCADE;

drop index  if exists CATALOG_ITEM_EXTENDS_FK;

drop index  if exists PRODUCT_PK;

drop table if exists PRODUCT  CASCADE;

drop index  if exists CATALOG_ITEM_EXTENDS3_FK;

drop index  if exists VEGETABLE_PK;

drop table if exists VEGETABLE  CASCADE;

drop index  if exists CATALOG_ITEM_EXTENDS2_FK;

drop index  if exists VEHICLE_PK;

drop table if exists VEHICLE  CASCADE;

drop domain if exists CATALOG_ITEM_TYPE CASCADE;

drop domain if exists CATALOG_TYPE CASCADE;

/*==============================================================*/
/* Domain: CATALOG_ITEM_TYPE                                    */
/*==============================================================*/
create domain CATALOG_ITEM_TYPE as VARCHAR(10);

comment on domain CATALOG_ITEM_TYPE is
'Ajouter ici les types d''items héritant de l''entité CATALOG_ITEM';

/*==============================================================*/
/* Domain: CATALOG_TYPE                                         */
/*==============================================================*/
create domain CATALOG_TYPE as VARCHAR(10);

comment on domain CATALOG_TYPE is
'Ajoutez ici les types de catalogues héritant de l''entité CATALOG';

/*==============================================================*/
/* Table: CATALOG_ASSOCIER                                      */
/*==============================================================*/
create table CATALOG_ASSOCIER (
   CATALOG_ITEM_ID      INT4                 not null,
   CATALOG_CATEGORY_ID  VARCHAR(80)          not null,
   constraint PK_CATALOG_ASSOCIER primary key (CATALOG_ITEM_ID, CATALOG_CATEGORY_ID)
);

/*==============================================================*/
/* Index: CATALOG_ASSOCIER_PK                                   */
/*==============================================================*/
create unique index CATALOG_ASSOCIER_PK on CATALOG_ASSOCIER (
CATALOG_ITEM_ID,
CATALOG_CATEGORY_ID
);

/*==============================================================*/
/* Index: CATALOG_ASSOCIER_FK                                   */
/*==============================================================*/
create  index CATALOG_ASSOCIER_FK on CATALOG_ASSOCIER (
CATALOG_ITEM_ID
);

/*==============================================================*/
/* Index: CATALOG_ASSOCIER2_FK                                  */
/*==============================================================*/
create  index CATALOG_ASSOCIER2_FK on CATALOG_ASSOCIER (
CATALOG_CATEGORY_ID
);

/*==============================================================*/
/* Table: CATALOG_CATEGORY                                      */
/*==============================================================*/
create table CATALOG_CATEGORY (
   CATALOG_CATEGORY_ID  VARCHAR(80)          not null,
   CATEGORY_DESC        VARCHAR(256)         not null,
   ITEM_TYPE            CATALOG_ITEM_TYPE    not null,
   constraint PK_CATALOG_CATEGORY primary key (CATALOG_CATEGORY_ID)
);

/*==============================================================*/
/* Index: CATALOG_CATEGORY_PK                                   */
/*==============================================================*/
create unique index CATALOG_CATEGORY_PK on CATALOG_CATEGORY (
CATALOG_CATEGORY_ID
);

/*==============================================================*/
/* Table: CATALOG_ENTRY                                         */
/*==============================================================*/
create table CATALOG_ENTRY (
   CATALOG_ENTRY_ID     SERIAL               not null,
   CATALOG_TYPE         CATALOG_TYPE         not null,
   constraint PK_CATALOG_ENTRY primary key (CATALOG_ENTRY_ID)
);

/*==============================================================*/
/* Index: CATALOG_ENTRY_PK                                      */
/*==============================================================*/
create unique index CATALOG_ENTRY_PK on CATALOG_ENTRY (
CATALOG_ENTRY_ID
);

/*==============================================================*/
/* Table: CATALOG_ITEM                                          */
/*==============================================================*/
create table CATALOG_ITEM (
   CATALOG_ITEM_ID      SERIAL               not null,
   CATALOG_ENTRY_ID     INT4                 not null,
   ITEM_TITLE           VARCHAR(80)          not null,
   ITEM_DESC            VARCHAR(256)         not null,
   CREATION_DATE        TIMESTAMP            not null,
   constraint PK_CATALOG_ITEM primary key (CATALOG_ITEM_ID)
);

/*==============================================================*/
/* Index: CATALOG_ITEM_PK                                       */
/*==============================================================*/
create unique index CATALOG_ITEM_PK on CATALOG_ITEM (
CATALOG_ITEM_ID
);

/*==============================================================*/
/* Index: CATALOG_ATTACHER_FK                                   */
/*==============================================================*/
create  index CATALOG_ATTACHER_FK on CATALOG_ITEM (
CATALOG_ENTRY_ID
);

/*==============================================================*/
/* Table: PRODUCT                                               */
/*==============================================================*/
create table PRODUCT (
   CATALOG_ITEM_ID      INT4                 not null,
   PRODUCT_ID           SERIAL               not null,
   PRICE                FLOAT8               not null,
   MONEY                VARCHAR(3)           not null,
   UNIT                 VARCHAR(16)          not null,
   QUANTITY             INT4                 null,
   constraint PK_PRODUCT primary key (CATALOG_ITEM_ID, PRODUCT_ID)
);

/*==============================================================*/
/* Index: PRODUCT_PK                                            */
/*==============================================================*/
create unique index PRODUCT_PK on PRODUCT (
CATALOG_ITEM_ID,
PRODUCT_ID
);

/*==============================================================*/
/* Index: CATALOG_ITEM_EXTENDS_FK                               */
/*==============================================================*/
create  index CATALOG_ITEM_EXTENDS_FK on PRODUCT (
CATALOG_ITEM_ID
);

/*==============================================================*/
/* Table: VEGETABLE                                             */
/*==============================================================*/
create table VEGETABLE (
   CATALOG_ITEM_ID      INT4                 not null,
   VEGETABLE_ID         SERIAL               not null,
   FAMILLE              VARCHAR(120)         not null,
   constraint PK_VEGETABLE primary key (CATALOG_ITEM_ID, VEGETABLE_ID)
);

/*==============================================================*/
/* Index: VEGETABLE_PK                                          */
/*==============================================================*/
create unique index VEGETABLE_PK on VEGETABLE (
CATALOG_ITEM_ID,
VEGETABLE_ID
);

/*==============================================================*/
/* Index: CATALOG_ITEM_EXTENDS3_FK                              */
/*==============================================================*/
create  index CATALOG_ITEM_EXTENDS3_FK on VEGETABLE (
CATALOG_ITEM_ID
);

/*==============================================================*/
/* Table: VEHICLE                                               */
/*==============================================================*/
create table VEHICLE (
   CATALOG_ITEM_ID      INT4                 not null,
   VEHICLE_ID           SERIAL               not null,
   TYPE                 VARCHAR(50)          not null,
   N_PLACES             INT4                 null,
   N_DOORS              INT4                 null,
   CONSUMPTION          FLOAT8               null,
   constraint PK_VEHICLE primary key (CATALOG_ITEM_ID, VEHICLE_ID)
);

/*==============================================================*/
/* Index: VEHICLE_PK                                            */
/*==============================================================*/
create unique index VEHICLE_PK on VEHICLE (
CATALOG_ITEM_ID,
VEHICLE_ID
);

/*==============================================================*/
/* Index: CATALOG_ITEM_EXTENDS2_FK                              */
/*==============================================================*/
create  index CATALOG_ITEM_EXTENDS2_FK on VEHICLE (
CATALOG_ITEM_ID
);

alter table CATALOG_ASSOCIER
   add constraint FK_CATALOG_ASSOCIER foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table CATALOG_ASSOCIER
   add constraint FK_CATALOG_ASSOCIER2 foreign key (CATALOG_CATEGORY_ID)
      references CATALOG_CATEGORY (CATALOG_CATEGORY_ID)
      on delete restrict on update restrict;

alter table CATALOG_ITEM
   add constraint FK_CATALOG_ATTACHER foreign key (CATALOG_ENTRY_ID)
      references CATALOG_ENTRY (CATALOG_ENTRY_ID)
      on delete restrict on update restrict;

alter table PRODUCT
   add constraint FK_CATALOG_ITEM_EXTENDS foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table VEGETABLE
   add constraint FK_CATALOG_ITEM_EXTENDS3 foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

alter table VEHICLE
   add constraint FK_CATALOG_ITEM_EXTENDS2 foreign key (CATALOG_ITEM_ID)
      references CATALOG_ITEM (CATALOG_ITEM_ID)
      on delete restrict on update restrict;

