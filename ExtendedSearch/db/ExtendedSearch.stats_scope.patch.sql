-- Database patch for ExtendedSearch 
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Stephan Muggli <muggli@hallowelt.biz>
-- @package    BlueSpice_Extensions
-- @subpackage ExtendedSearch
-- @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

ALTER TABLE /*$wgDBprefix*/bs_searchstats MODIFY COLUMN stats_scope varchar(11) NULL DEFAULT NULL;