CREATE TABLE `ptb_canonical_list` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`ACTIVE` varchar(1) NOT NULL,
	`DATE_CREATE` datetime NOT NULL,
	`CREATED_BY` int NOT NULL,
	`TIMESTAMP_X` timestamp NOT NULL,
	`MODIFIED_BY` int NOT NULL,
	`SITE_ID` varchar(2) NOT NULL,
	`PAGE` varchar(255) NOT NULL,
	`CANONICAL` varchar(255) NOT NULL,
	`USE_REGEXP` varchar(1) NOT NULL,
	`SORT` int(11) NOT NULL,
	PRIMARY KEY(`ID`)
);