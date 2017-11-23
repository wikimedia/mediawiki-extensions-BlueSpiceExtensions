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

CREATE OR REPLACE FUNCTION insertfile_getImagePosition (VARCHAR(255)) RETURNS INTEGER AS '
	DECLARE
		filename ALIAS FOR $1;
		row INTEGER := 0;
		tmp RECORD;
	BEGIN
		FOR tmp IN SELECT i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime = ''image'' OR i.img_minor_mime = ''tiff'')
			ORDER BY i.img_name ASC LOOP

			row=row+1;

			IF tmp.img_name = filename THEN
				RETURN row;
			END IF;
			
		END LOOP;
	END;
' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION insertfile_getFilePosition (VARCHAR(255)) RETURNS INTEGER AS '
	DECLARE
		filename ALIAS FOR $1;
		row INTEGER := 0;
		tmp RECORD;
	BEGIN
		FOR tmp IN SELECT i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime != ''image'' AND i.img_minor_mime != ''tiff'')
			ORDER BY i.img_name ASC LOOP

			row=row+1;

			IF tmp.img_name = filename THEN
				RETURN row;
			END IF;
			
		END LOOP;
	END;
' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION insertfile_getImageUploadPosition () RETURNS INTEGER AS '
	DECLARE
		filename mediawiki.image.img_name%TYPE;
		row INTEGER := 0;
		tmp RECORD;
	BEGIN
		SELECT INTO filename i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime = ''image'' OR i.img_minor_mime = ''tiff'')
			ORDER BY i.img_timestamp DESC
			LIMIT 1;

		FOR tmp IN SELECT i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime = ''image'' OR i.img_minor_mime = ''tiff'')
			ORDER BY i.img_name ASC LOOP

			row=row+1;

			IF tmp.img_name = filename THEN
				RETURN row;
			END IF;
			
		END LOOP;
	END;
' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION insertfile_getFileUploadPosition () RETURNS INTEGER AS '
	DECLARE
		filename mediawiki.image.img_name%TYPE;
		row INTEGER := 0;
		tmp RECORD;
	BEGIN
		SELECT INTO filename i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime != ''image'' AND i.img_minor_mime != ''tiff'')
			ORDER BY i.img_timestamp DESC
			LIMIT 1;

		FOR tmp IN SELECT i.img_name
			FROM mediawiki.image i
			WHERE (i.img_major_mime != ''image'' AND i.img_minor_mime != ''tiff'')
			ORDER BY i.img_name ASC LOOP

			row=row+1;

			IF tmp.img_name = filename THEN
				RETURN row;
			END IF;
			
		END LOOP;
	END;
' LANGUAGE 'plpgsql';