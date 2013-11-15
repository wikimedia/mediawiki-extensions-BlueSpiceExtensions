CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_dashboards_configs (
	--Allows to store different types of portals: i.e. 'user', 'admin', 'tag'
	dc_type       varchar(30) binary NOT NULL DEFAULT 'user', 
	-- Depends on 'dc_type'. May be a user.user_id or a combination of 
	-- page.page_id and a tag identifier
	dc_identifier varchar(100) binary NOT NULL DEFAULT '0',
	-- This stores the portal config as a JSON string
	dc_config     mediumblob NOT NULL,
	-- This is for future use and may allow some simple verisioning mechanism
	dc_timestamp  binary(14),
	PRIMARY KEY (dc_type,dc_identifier,dc_timestamp)
) /*$wgDBTableOptions*/ COMMENT='BlueSpice: Dashboards - Stores dashboard configs';