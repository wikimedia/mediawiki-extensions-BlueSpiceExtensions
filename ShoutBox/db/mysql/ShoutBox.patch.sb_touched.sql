ALTER TABLE  /*$wgDBprefix*/bs_shoutbox ADD sb_touched varchar(14) NOT NULL default '';
UPDATE /*$wgDBprefix*/bs_shoutbox SET sb_touched = sb_timestamp;