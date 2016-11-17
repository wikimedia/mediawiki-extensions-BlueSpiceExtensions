<?php
/**
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Dejan Savuljesku <savuljesku@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
class TagCloudSearchStatsHandler extends TagCloudBaseHandler {
  public static function collectData( $aOptions, $aData = array(), Title $oTitle = null ) {
    $oDB = wfGetDB( DB_SLAVE );
    $aTables = array( 'bs_searchstats' );
    $aFields = array(
      'stats_term',
      'count' => 'COUNT(stats_term)'
    );

    $sTimeSpan = "1 month";
    if ( !empty( $aOptions['timespan'] ) ) {
      $sTimeSpan = $aOptions['timespan'];
    }

    $sStartDate = wfTimestamp( TS_MW, strtotime( '-'.$sTimeSpan ) );

    $aConditions = array();
    $aConditions[] = "stats_ts >= $sStartDate";

    if ( !empty( $aOptions['scope'] ) ) {
        $sScope =  $aOptions['scope'];
        $aConditions[] = "stats_scope = '$sScope'";
    }

    $aQueryOptions = array(
      'GROUP BY' => 'stats_term',
      'ORDER BY' => 'COUNT(stats_term) DESC'
    );

    if ( $aOptions['count'] != -1 ) {
      $aQueryOptions['LIMIT'] = $aOptions["count"];
    }

    $oRes = $oDB->select(
        $aTables,
        $aFields,
        $aConditions,
        __METHOD__,
        $aQueryOptions
    );

    if( !$oRes ) {
      return array();
    }

    $aData = array();

    $oSpecialPage = Title::makeTitle( NS_SPECIAL, 'ExtendedSearch' );

    foreach ( $oRes as $oRow ) {
      $sNormalized = self::normalizeTerm( $oRow->stats_term );

      if ( array_key_exists( $sNormalized, $aData ) ) {
        $aData[$sNormalized]->count += $oRow->count;
      } else {
        $aData[$sNormalized] = (object) array(
          'tagname' => $sNormalized,
          'count' => $oRow->count,
          'link' => $oSpecialPage->getLocalUrl( array( 'q' => $sNormalized ) )
        );
      }
    }

    return $aData;
  }

  private static function normalizeTerm( $term ) {
      $term = preg_replace( "/(\\\)/", "", $term ); //'term\\.com' -> 'term.com'
      $term = preg_replace( "/(\*)/", "", $term ); //'*term*' -> 'term'
      $term = preg_replace( "/(~.*)/", "", $term ); //'term~0.5' -> 'term'
      $term = preg_replace( "/(\"*)/", "", $term ); //'"term"' -> 'term'
      $term = preg_replace( "/(\%20*)/", " ", $term); //'term1%20term2' -> 'term1 term2'
      $term = preg_replace( "/(\%c3%b6*)/i", "ö", $term); //'sch%c3%b6n' -> 'schön'
      $term = preg_replace( "/(\%c3%96*)/i", "Ö", $term); //'sch%c3%b6n' -> 'schön'
      $term = preg_replace( "/(\%c3%bc*)/i", "ü", $term); //'t%c3%bcr' -> 'tür'
      $term = preg_replace( "/(\%c3%9c*)/i", "Ü", $term); //'t%c3%bcr' -> 'tür'
      $term = preg_replace( "/(\%c3%a4*)/i", "ä", $term); //'b%c3%a4r' -> 'bär'
      $term = preg_replace( "/(\%c3%84*)/i", "Ä", $term); //'b%c3%a4r' -> 'bär'
      $term = preg_replace( "/(\%c3%9F*)/i", "ß", $term); //'spa%c3%9F' -> 'spaß'

      $term = trim( $term ); //' term  ' -> 'term'

      return $term;
  }

}
