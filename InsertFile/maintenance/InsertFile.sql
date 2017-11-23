-- Database definitions for InsertFile
--
-- Part of BlueSpice MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>

-- @package    BlueSpice_Extensions
-- @subpackage InsertFile
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
-- @filesource

DROP PROCEDURE IF EXISTS `insertfile_getFilePosition`;
DELIMITER //
CREATE PROCEDURE `insertfile_getFilePosition`(filename VARCHAR(255))
BEGIN
    SELECT tmp.rank FROM
        (SELECT @row:=@row+1 rank, i.img_name
         FROM [REPLACE_WITH_TABLE_PREFIX]image i, (SELECT @row:=0) r
         WHERE (i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')
         ORDER BY i.img_name ASC) tmp
    WHERE tmp.img_name = filename;
END //
DELIMITER;

DROP PROCEDURE IF EXISTS `insertfile_getFileUploadPosition`;
DELIMITER //
CREATE PROCEDURE `insertfile_getFileUploadPosition`()
BEGIN
    SELECT tmp.rank FROM
        (SELECT @row:=@row+1 rank, i.img_name, i.img_timestamp
         FROM [REPLACE_WITH_TABLE_PREFIX]image i, (SELECT @row:=0) r
         WHERE (i.img_major_mime != 'image' AND i.img_minor_mime != 'tiff')
         ORDER BY i.img_name ASC) tmp
    ORDER BY tmp.img_timestamp DESC
    LIMIT 1;
END //
DELIMITER;

DROP PROCEDURE IF EXISTS `insertfile_getImagePosition`;
DELIMITER //
CREATE PROCEDURE `insertfile_getImagePosition`(filename VARCHAR(255))
BEGIN
    SELECT tmp.rank FROM
        (SELECT @row:=@row+1 rank, i.img_name
         FROM [REPLACE_WITH_TABLE_PREFIX]image i, (SELECT @row:=0) r
         WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
         ORDER BY i.img_name ASC) tmp
    WHERE tmp.img_name = filename;
END //
DELIMITER;

DROP PROCEDURE IF EXISTS `insertfile_getImageUploadPosition`;
DELIMITER //
CREATE PROCEDURE `insertfile_getImageUploadPosition`()
BEGIN
    SELECT tmp.rank FROM
        (SELECT @row:=@row+1 rank, i.img_name, i.img_timestamp
         FROM [REPLACE_WITH_TABLE_PREFIX]image i, (SELECT @row:=0) r
         WHERE (i.img_major_mime = 'image' OR i.img_minor_mime = 'tiff')
         ORDER BY i.img_name ASC) tmp
    ORDER BY tmp.img_timestamp DESC
    LIMIT 1;
END //
DELIMITER;