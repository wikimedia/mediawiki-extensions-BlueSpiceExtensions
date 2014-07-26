<?php

/**
 * NamespacerNuker
 * @author Sebastian Ulbricht
 */
// Last review MRG (01.07.11 01:47)
class NamespaceNuker {

	protected static function PurgeRedundantText() {
		global $wgDBtype;
		$dbw = wfGetDB(DB_MASTER);
		$dbw->begin();

		$tbl_arc = $dbw->tableName('archive');
		$tbl_rev = $dbw->tableName('revision');
		$tbl_txt = $dbw->tableName('text');

		# Get "active" text records from the revisions table
		$res = $dbw->query("SELECT DISTINCT rev_text_id FROM $tbl_rev");
		while ($row = $dbw->fetchObject($res)) {
			$cur[] = $row->rev_text_id;
		}

		# Get "active" text records from the archive table
		$res = $dbw->query("SELECT DISTINCT ar_text_id FROM $tbl_arc");
		while ($row = $dbw->fetchObject($res)) {
			$cur[] = $row->ar_text_id;
		}

		# Get the IDs of all text records not in these sets
		$set = implode(', ', $cur);
		$res = $dbw->query("SELECT old_id FROM $tbl_txt WHERE old_id NOT IN ( $set )");
		$old = array();
		while ($row = $dbw->fetchObject($res)) {
			$old[] = $row->old_id;
		}
		if (count($old)) {
			$set = implode(', ', $old);

			$tbl_txt_bck = $wgDBtype == 'oracle' ? $dbw->tableName('bs_ns_bak_text') : $dbw->tableName('bs_namespacemanager_backup_text');
			$dbw->query("INSERT INTO $tbl_txt_bck SELECT * FROM $tbl_txt WHERE old_id IN ($set)");
			$dbw->query("DELETE FROM $tbl_txt WHERE old_id IN ( $set )");
		}

		$dbw->commit();
	}

	protected static function DeleteRevisions($revs) {
		global $wgDBtype;
		$dbw = wfGetDB(DB_MASTER);
		$dbw->begin();
		$tbl_rev = $dbw->tableName('revision');
		if (count($revs)) {
			$set = implode(', ', $revs);
			$tbl_rev_bck = $wgDBtype == 'oracle' ? $dbw->tableName('bs_ns_bak_revision') : $dbw->tableName('bs_namespacemanager_backup_revision');
			$dbw->query("INSERT INTO $tbl_rev_bck SELECT * FROM $tbl_rev WHERE rev_id IN ( $set )");
			$dbw->query("DELETE FROM $tbl_rev WHERE rev_id IN ( $set )");
		}
		$dbw->commit();
	}

	public static function removeAllPages($idNS, $nameNS, $toNS = 0) {
		global $wgDBtype;
		if (!$idNS)
			return false;
		$bnUser = RequestContext::getMain()->getUser();
		$idUser = $bnUser->getId();
		$nameUser = $bnUser->getName();

		$dbw = wfgetDB(DB_MASTER);
		$dbw->begin();
		$tbl_pag = $dbw->tableName('page');
		$tbl_rec_chg = $dbw->tableName('recentchanges');
		$idNS = $dbw->addQuotes($idNS);
		$pages = array();
		$info = array();
		$renamed = 0;

		$res = $dbw->query("SELECT page_id, page_title, page_len, page_latest " .
						"FROM $tbl_pag " .
						"WHERE page_namespace = $idNS");
		while ($row = $dbw->fetchObject($res)) {
			$pages[] = $row->page_title;
			$info[$row->page_title] = array('page_title' => $row->page_title,
				'page_id' => $row->page_id,
				'last_id' => $row->page_latest,
				'page_len' => $row->page_len);
		}
		if (count($pages)) {
			$set = implode('\', \'', $pages);
			$res = $dbw->query("SELECT page_title " .
							"FROM $tbl_pag " .
							"WHERE page_namespace = 0 " .
							"  AND page_title IN ('$set')");
			$pages = array();
			while ($row = $dbw->fetchObject($res)) {
				$pages[] = $row->page_title;
				$info[$row->page_title]['page_title'] = $row->page_title . "_(from_$nameNS)";
			}
			if (count($pages)) {
				$set = implode('\', \'', $pages);
				$dbw->query("UPDATE $tbl_pag " .
						"SET page_title = CONCAT(page_title, '_(from_$nameNS)') " .
						"WHERE page_namespace = $idNS " .
						"  AND page_title IN ('$set')");
				$renamed = $set;
			}
			if ($wgDBtype == 'postgres') {
				$time = wfTimestamp(TS_POSTGRES, time());
			} else {
				$time = date("YmdHis", time());
			}
			foreach ($info as $page) {
				$dbw->query("INSERT INTO $tbl_rec_chg " .
						"(rc_timestamp, rc_cur_time, rc_user, rc_user_text, rc_namespace, rc_title, rc_comment, rc_minor ,rc_bot, rc_new, rc_cur_id, rc_this_oldid, " .
						"rc_last_oldid, rc_type, rc_moved_to_ns, rc_patrolled, rc_ip, rc_old_len, rc_new_len, rc_deleted, rc_logid, rc_log_type) VALUES " .
						"('$time', '$time', '$idUser', '$nameUser', $idNS, '" . $page['page_title'] . "', " .
						// TODO SU (04.07.11 12:05): i18n
						"'Diese Seite wurde vom Namespace \"$nameNS\" in den Mainspace verschoben, da der Namespace \"$nameNS\" gelöscht wurde.', " .
						"0, 0, 0, " . $page['page_id'] . ", " . $page['last_id'] . ", 0, 3, 0, 0, '" . $_SERVER['REMOTE_ADDR'] . "', " . $page['page_len'] . ", " .
						$page['page_len'] . ", 0, 0, 'move')");
			}
		}
		$dbw->query("UPDATE $tbl_pag " .
				"SET page_namespace = $toNS " .
				"WHERE page_namespace = $idNS");
		$dbw->query("UPDATE $tbl_rec_chg " .
				"SET rc_namespace = $toNS " .
				"WHERE rc_namespace = $idNS");
		$dbw->commit();

		if ($renamed != 0) {
			return $renamed;
		}
		return true;
	}

	public static function removeAllPagesWithSuffix($idNS, $nameNS, $toNS = 0) {
		if (!$idNS) {
			return false;
		}

		$dbw = wfgetDB(DB_MASTER);
		$dbw->begin();
		$tbl_pag = $dbw->tableName('page');
		$tbl_rec_chg = $dbw->tableName('recentchanges');

		$res = $dbw->query("SELECT page_id, page_title, page_len, page_latest " .
						"FROM $tbl_pag " .
						"WHERE page_namespace = $idNS");
		$info = array();
		while ($row = $dbw->fetchObject($res)) {
			$info[$row_ > page_title] = array('page_title' => $row->page_title . "_(from_$nameNS)",
				'page_id' => $row->page_id,
				'last_id' => $row->page_latest,
				'page_len' => $row->page_len);
		}
		if ($wgDBtype == 'postgres') {
			$time = wfTimestamp(TS_POSTGRES, time());
		} else {
			$time = date("YmdHis", time());
		}
		foreach ($info as $page) {
			$dbw->query("INSERT INTO $tbl_rec_chg " .
					"(rc_timestamp, rc_cur_time, rc_user, rc_user_text, rc_namespace, rc_title, rc_comment, rc_minor ,rc_bot, rc_new, rc_cur_id, rc_this_oldid, " .
					"rc_last_oldid, rc_type, rc_moved_to_ns, rc_patrolled, rc_ip, rc_old_len, rc_new_len, rc_deleted, rc_logid, rc_log_type) VALUES " .
					"('$time', '$time', '$idUser', '$nameUser', $idNS, '" . $page['page_title'] . "', " .
					// TODO SU (04.07.11 12:05): i18n
					"'Diese Seite wurde vom Namespace \"$nameNS\" in den Mainspace verschoben, da der Namespace \"$nameNS\" gelöscht wurde.', " .
					"0, 0, 0, " . $page['page_id'] . ", " . $page['last_id'] . ", 0, 3, 0, 0, '" . $_SERVER['REMOTE_ADDR'] . "', " . $page['page_len'] . ", " .
					$page['page_len'] . ", 0, 0, 'move')");
		}

		$dbw->query("UPDATE $tbl_pag " .
				"SET page_title = CONCAT(page_title, '_(from_$nameNS)'), " .
				"    page_namespace = $toNS " .
				"WHERE page_namespace = $idNS");
		$dbw->query("UPDATE $tbl_rec_chg " .
				"SET rc_namespace = $toNS " .
				"WHERE rc_namespace = $idNS");

		$dbw->commit();
		return true;
	}

	public static function nukeNamespaceWithAllPages($idNS) {
		global $wgDBtype;
		if (!$idNS) {
			return false;
		}
		$dbw = wfgetDB(DB_MASTER);
		$dbw->begin();

		$tbl_pag = $dbw->tableName('page');
		$tbl_rev = $dbw->tableName('revision');
		$tbl_pag_bck = $wgDBtype == 'oracle' ? $dbw->tableName('bs_ns_bak_page') : $dbw->tableName('bs_namespacemanager_backup_page');
		$tbl_rec_chg = $dbw->tableName('recentchanges');
		$tbl_src_idx = $dbw->tableName('searchindex');
		$res = $dbw->query("SELECT page_title FROM $tbl_pag WHERE page_namespace = $idNS");
		$i_deleted = 0;

		while ($row = $dbw->fetchObject($res)) {
			$title = Title::newFromText($row->page_title, $idNS);
			$id = $title->getArticleID();

			// Get corresponding revisions
			$res2 = $dbw->query("SELECT rev_id FROM $tbl_rev WHERE rev_page = $id");
			$revs = array();
			while ($row2 = $dbw->fetchObject($res2)) {
				$revs[] = $row2->rev_id;
			}
			$dbw->query("INSERT INTO $tbl_pag_bck SELECT * FROM $tbl_pag WHERE page_id = $id");
			// Delete revisions as appropriate
			self::DeleteRevisions($revs);
			$dbw->query("DELETE FROM $tbl_pag WHERE page_id = $id");
			$dbw->query("DELETE FROM $tbl_src_idx WHERE si_page = $id");

			$i_deleted++;
		}
		self::PurgeRedundantText(true);
		$dbw->query("DELETE FROM $tbl_rec_chg WHERE rc_namespace = $idNS");
		$dbw->commit();
		if ($i_deleted > 0) {
			$res = $dbw->query("SELECT COUNT(*) AS pages FROM $tbl_pag");
			$row = $dbw->fetchObject($res);
			$pages = $row->pages;
			$dbw->update(
					'site_stats', array('ss_total_pages' => $pages), array('ss_row_id' => 1), __METHOD__
			);
		}
		
		return true;
	}

}