create table if not exists sc_collections
(
    ID int(11) NOT NULL auto_increment,
	ACTIVE char(1) not null default 'Y',
	CHECK_PARENT char(1) not null DEFAULT 'N',
	IBLOCK_ID int(11),
	SECTION_ID int(11),
	CONDITIONS text,
	UNPACK text,
	DATE_GENERATION datetime,
	COLLECTION_IBLOKS text,
	CATALOG_AVAILABLE char(1),
	IS_SECTION_ACTIVE_UPDATE char(1),
	TYPE_ID varchar(2) not null,
	FILTER text,
	DISCOUNT_ACTION varchar(3) not null,
	PRIMARY KEY(ID),
	INDEX ix_scoder_collections_oi (SECTION_ID)
);