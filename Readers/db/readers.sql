CREATE TABLE IF NOT EXISTS /*_*/bs_readers (
	readers_id INT(30) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	readers_user_id INT(30) UNSIGNED NOT NULL,
	readers_user_name varbinary(255) NOT NULL DEFAULT '',
	readers_page_id INT(30) UNSIGNED NOT NULL,
	readers_rev_id INT(30) UNSIGNED NOT NULL,
	readers_ts varchar(16) NOT NULL DEFAULT ''
) /*$wgDBTableOptions*/
COMMENT='BlueSpice: Readers - Stores information on users activities';

CREATE INDEX /*i*/readers_user_id ON /*_*/bs_readers (readers_user_id);
CREATE INDEX /*i*/readers_page_id ON /*_*/bs_readers (readers_page_id);
CREATE INDEX /*i*/readers_rev_id ON /*_*/bs_readers (readers_rev_id);
CREATE INDEX /*i*/readers_user_name ON /*_*/bs_readers (readers_user_name);
CREATE INDEX /*i*/readers_ts ON /*_*/bs_readers (readers_ts);