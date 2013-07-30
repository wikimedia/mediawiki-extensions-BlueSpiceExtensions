ALTER TABLE  /*$wgDBprefix*/bs_shoutbox 
	ADD sb_parent_id int(5) unsigned NOT NULL default 0,
	ADD INDEX /*i*/sb_parent_id (sb_parent_id);