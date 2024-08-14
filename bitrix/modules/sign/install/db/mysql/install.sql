create table if not exists b_sign_blank
(
	ID int(18) not null auto_increment,
	TITLE varchar(255) not null,
	EXTERNAL_ID int(18) default null,
	HOST varchar(255) default null,
	FILE_ID text not null,
	STATUS text null,
	CONVERTED char(1) not null default 'N',
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_EXTERNAL_HOST (EXTERNAL_ID, HOST)
);

create table if not exists b_sign_document
(
	ID int(18) not null auto_increment,
	TITLE varchar(255) default null,
	HASH char(19) default null,
	SEC_CODE char(20) default null,
	HOST varchar(255) default null,
	BLANK_ID int(18) not null,
	SCENARIO tinyint unsigned null,
	UID varchar(20) null,
	STATUS varchar(10) null,
	ENTITY_TYPE varchar(20) not null,
	ENTITY_ID int(18) not null,
	META text default null,
	PROCESSING_STATUS char(1) not null default 'B',
	PROCESSING_ERROR text default null,
	LANG_ID char(2) default null,
	RESULT_FILE_ID int(18) default null,
	VERSION tinyint unsigned default 1,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	DATE_SIGN timestamp not null default 0,
	PRIMARY KEY(ID),
	INDEX IX_B_ENTITY (ENTITY_TYPE, ENTITY_ID),
	INDEX IX_B_HOST (HOST),
	UNIQUE UK_SIGN_DOCUMENT_HASH (HASH)
	);

create table if not exists b_sign_member
(
	ID bigint unsigned not null auto_increment,
	DOCUMENT_ID bigint unsigned not null,
	CONTACT_ID bigint unsigned not null,
	PART int(2) not null,
	HASH char(32) not null,
	SIGNED char(1) not null default 'N',
	VERIFIED char(1) not null default 'N',
	MUTE char(1) not null default 'N',
	COMMUNICATION_TYPE varchar(20),
	COMMUNICATION_VALUE varchar(100),
	USER_DATA text,
	META text default null,
	SIGNATURE_FILE_ID int(18) default null,
	STAMP_FILE_ID int(18) default null,
    CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	DATE_SIGN timestamp not null default 0,
	DATE_DOC_DOWNLOAD timestamp not null default 0,
	DATE_DOC_VERIFY timestamp not null default 0,
	IP varchar(15) null,
	TIME_ZONE_NAME varchar(50) null,
	TIME_ZONE_OFFSET int(18) null,
	ENTITY_ID bigint unsigned null,
	ENTITY_TYPE varchar(20) null,
	PRESET_ID bigint unsigned null,
	PRIMARY KEY(ID),
	INDEX IX_B_DOCUMENT_ID (DOCUMENT_ID),
	INDEX IX_B_HASH (HASH)
);

create table if not exists b_sign_block
(
	ID int(18) not null auto_increment,
	CODE varchar(50) not null,
	TYPE varchar(20) null,
	BLANK_ID int(18) not null,
	BLANK_POSITION text not null,
	BLANK_STYLE text default null,
	BLANK_DATA text not null,
	PART int(2) not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_BLANK_ID (BLANK_ID),
	INDEX IX_B_PART (BLANK_ID, PART)
);

create table if not exists b_sign_integration_form
(
	ID int(18) not null auto_increment,
	BLANK_ID int(18) not null,
	PART int(2) not null,
	FORM_ID int(18) not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_BLANK_PART (BLANK_ID, PART)
);

create table if not exists b_sign_documentgenerator_blank
(
	ID bigint unsigned not null auto_increment,
	BLANK_ID bigint unsigned not null,
	DOCUMENT_GENERATOR_TEMPLATE_ID bigint unsigned unique not null,
	INITIATOR  VARCHAR(1024)          null,
	CREATED_AT timestamp default NOW(),
	PRIMARY KEY(ID)
);

create table if not exists `b_sign_permission` (
   `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
   `ROLE_ID` INT UNSIGNED NOT NULL,
   `PERMISSION_ID` VARCHAR(32) NOT NULL DEFAULT '',
   `VALUE` VARCHAR(32) NOT NULL DEFAULT '',
   PRIMARY KEY (`ID`),
   CONSTRAINT `IX_SIGN_PERMISSION_ROLE_ID_PERMISSION_ID` UNIQUE
       (`ROLE_ID`, `PERMISSION_ID`)
);