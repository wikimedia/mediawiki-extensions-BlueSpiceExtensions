CREATE TABLE /*_*/bs_pageassignments (
	pa_page_id int unsigned,
	pa_assignee_key varchar(255) binary NOT NULL default '',
	pa_assignee_type varchar(255) binary NOT NULL default '',
	pa_position int unsigned NOT NULL
) /*$wgDBTableOptions*/
COMMENT='BlueSpice: PageAssignments - Stores assignments for certain articles';

CREATE INDEX /*i*/pa_page_id ON /*_*/bs_pageassignments (pa_page_id);