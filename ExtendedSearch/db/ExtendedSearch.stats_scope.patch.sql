-- Database patch for ExtendedSearch 
--
-- Part of BlueSpice MediaWiki
--
-- @author     Stephan Muggli <muggli@hallowelt.com>
-- @package    BlueSpice_Extensions
-- @subpackage ExtendedSearch
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

ALTER TABLE /*$wgDBprefix*/bs_searchstats MODIFY COLUMN stats_scope varchar(11) NULL DEFAULT NULL;