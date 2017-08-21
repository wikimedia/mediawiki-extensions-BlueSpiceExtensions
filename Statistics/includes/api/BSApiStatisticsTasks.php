<?php

class BSApiStatisticsTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'getData' => [
			'params' => [
				'examples' => [
					[
						'diagram' => 'BsDiagramNumberOfPages',
						'grain' => 'Y',
						'from' => '01.01.2017',
						'mode' => 'list',
						'to' => '31.12.2017'
					]
				],
				'diagram' => [
					'desc' => 'Diagram name',
					'type' => 'string',
					'required' => true
				],
				'grain' => [
					'desc' => 'Valid grain type',
					'type' => 'string',
					'required' => true
				],
				'from' => [
					'desc' => 'Date in format d.m.Y',
					'type' => 'string',
					'required' => true
				],
				'mode' => [
					'desc' => 'Valid mode name',
					'type' => 'string',
					'required' => true
				],
				'to' => [
					'desc' => 'Date in format d.m.Y',
					'type' => 'string',
					'required' => true
				]
			]
		]
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'getData' => array( 'read' )
		);
	}

	public function task_getData( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$sDiagram	= $oTaskData->diagram;
		$sGrain		= $oTaskData->grain;
		$sFrom		= $oTaskData->from;
		$sMode		= $oTaskData->mode;
		$sTo		= $oTaskData->to;

		$aAvailableDiagrams = Statistics::getAvailableDiagrams();
		$aAllowedDiaKeys = array_keys( $aAvailableDiagrams );

		if( empty( $sDiagram ) ) {
			$oResponse->errors['inputDiagrams'] = wfMessage( 'bs-statistics-err-emptyinput' )->plain();
		}
		if( !in_array( $sDiagram, $aAllowedDiaKeys ) ) {
			$oResponse->errors['inputDiagrams'] = wfMessage( 'bs-statistics-err-unknowndia' )->plain();
		}

		if( !array_key_exists( $sGrain, BsConfig::get( 'MW::Statistics::AvailableGrains' ) ) ) {
			$oResponse->errors['InputDepictionGrain'] = wfMessage( 'bs-statistics-err-unknowngrain' )->plain();
		}

		if( empty( $sFrom ) ) {
			$oResponse->errors['inputFrom'] = wfMessage( 'bs-statistics-err-emptyinput' )->plain();
		}
		if( !$oFrom = DateTime::createFromFormat( 'd.m.Y', $sFrom ) ) {
			$oResponse->errors['inputFrom'] = wfMessage( 'bs-statistics-err-invaliddate' )->plain();
		}


		if( empty( $sTo ) ) {
			$oResponse->errors['inputTo'] = wfMessage( 'bs-statistics-err-emptyinput' )->plain();
		}
		if( !$oTo = DateTime::createFromFormat( 'd.m.Y', $sFrom ) ) {
			$oResponse->errors['inputTo'] = wfMessage( 'bs-statistics-err-invaliddate' )->plain();
		}
		if( $oTo > new DateTime() ) {
			$oResponse->errors['inputTo'] = wfMessage( 'bs-statistics-err-invaliddate' )->plain();
		}

		if( isset($oFrom) && isset($oTo) && $oFrom > $oTo ) {
			$oResponse->errors['inputTo'] = wfMessage( 'bs-statistics-err-invalidtofromrelation' )->plain();
		}

		if( empty( $sMode ) ) {
			$oResponse->errors['rgInputDepictionMode'] = wfMessage( 'bs-statistics-err-emptyinput' )->plain();
		}
		if( !in_array( $sMode, array('absolute', 'aggregated', 'list') ) ) {
			$oResponse->errors['rgInputDepictionMode'] = wfMessage( 'bs-statistics-err-unknownmode' )->plain();
		}
		if( !isset( $oResponse->errors['inputDiagrams'])
			&& $sMode == 'list'
			&& !$aAvailableDiagrams[$sDiagram]->isListable() ) {
			$oResponse->errors['rgInputDepictionMode'] = wfMessage( 'bs-statistics-err-modeunsupported' )->plain();
		}

		if( !empty( $oResponse->errors ) ) {
			return $oResponse;
		}

		$oDiagram = Statistics::getDiagram( $sDiagram );
		$oDiagram->setStartTime( $sFrom );
		$oDiagram->setEndTime( $sTo );
		$oDiagram->setActualGrain( $sGrain );
		$oDiagram->setModLabel( $sGrain );
		$oDiagram->setMode( $sMode );
		//$oDiagram->setMessage( $sMessage );
		//$oDiagram->setFilters( $aDiagFilter );

		switch ( $oDiagram->getActualGrain() ) {
			// Here, only those grains are listed where label code differs from grain code.
			case 'W' : $oDiagram->setModLabel( 'W/y' ); break;
			case 'm' : $oDiagram->setModLabel( 'M y' ); break;
			case 'd' : $oDiagram->setModLabel( 'd.m' ); break;
			//default  : $oDiagram->modLabel = false;
		}

		switch ( $oDiagram->getDataSource() ) {
			case BsDiagram::DATASOURCE_DATABASE :
				global $wgDBtype, $wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname;
				switch( $wgDBtype ) {
					case "postgres" : $oReader = new PostGreSQLDbReader(); break;
					case "oracle"   : $oReader = new OracleDbReader(); break;
					default         : $oReader = new MySQLDbReader();
				}
				//$oReader = $sDbType == 'mysql' ? new MySQLDbReader() : new PostGreSQLDbReader();
				$oReader->host = $wgDBserver;
				$oReader->user = $wgDBuser;
				$oReader->pass = $wgDBpassword;
				$oReader->db   = $wgDBname;
				break;
		}

		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		if( count( $intervals ) > BsConfig::get( 'MW::Statistics::MaxNumberOfIntervals' ) ) {
			$oResponse->message = wfMessage( 'bs-statistics-interval-too-big' )->plain();
			return $oResponse;
		}

		global $wgDBtype;
		// Pls. keep the space after user, otherwise, user_groups is also replaced
		$sql = $oDiagram->getSQL();
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__user', '#__mwuser', $sql );
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__mwuser_', '#__user_', $sql );
		global $wgDBprefix;
		$sql = str_replace( "#__", $wgDBprefix, $sql );

		foreach ( $oDiagram->getFilters() as $oFilter ) {
			$oFilter->getValueFromTaskData( $oTaskData );
			$sFilterSql = $oFilter->getSql();
			$sql = str_replace( $oFilter->getSqlKey(), $sFilterSql, $sql );
		}

		$oReader->match = $sql;
		$oDiagram->setData( BsCharting::getDataPerDateInterval( $oReader, $oDiagram->getMode(), $intervals, $oDiagram->isListable() ) );

		if ( $oDiagram->isList() ) {
			BsCharting::prepareList( $oDiagram, $oReader );
			$aDatas = $oDiagram->getData();
			$aLabels = $oDiagram->getListLabel();

			$aFields = array();
			$aColumns = array();
			foreach( $aLabels as $sLabel ) {
				$sField = strtolower( $sLabel );
				$sField = str_replace( " ", "_", $sField );
				$sField = str_replace( ".", "", $sField );
				$aFields[] = array( 'name' => $sField );
				$aColumns[] = array( 'header' => $sLabel, 'dataIndex' => $sField );
			}

			$aList = array();
			$aTypes = array();
			foreach( $aDatas as $aData ){
				$aItem = array();
				for( $i = 0; $i < count( $aData ); $i++ ) {
					if( $this->isInt( $aData[ $i ] ) ) {
						$aTypes[ $aFields[ $i ][ 'name' ] ][] = 'int';
					} else {
						$aTypes[ $aFields[ $i ][ 'name' ] ][] = 'string';
					}
					$aItem[ $aFields[ $i ][ 'name' ] ] = $aData[ $i ];
				}
				$aList[ 'items' ][] = $aItem;
			}

			foreach( $aTypes as $key=>$value ) {
				$sColumnType = "string";
				if( count( array_unique( $value ) ) === 1 ) {
					$sColumnType = $value[0];
				}

				for( $i = 0; $i < count( $aFields ); $i++ ) {
					if( $aFields[ $i ][ 'name' ] === $key ) {
						$aFields[ $i ][ 'type' ] = $sColumnType;
					}
				}
			}

			$oResponse->payload['data']['list'] = $aList;
			$oResponse->payload['data']['fields'] = $aFields;
			$oResponse->payload['data']['columns'] = $aColumns;
			$oResponse->payload['label'] = $oDiagram->getTitle();
			$oResponse->success = true;
			return $oResponse;
		}

		$aData = $oDiagram->getData();
		$i = 0;
		foreach ( $intervals as $interval ) {
			$oResponse->payload['data'][] = array(
				'name' => $interval->getLabel(),
				'hits' => (int)$aData[$i],
			);
			$i ++;
		}

		$aAvalableGrains = BsConfig::get( 'MW::Statistics::AvailableGrains' );
		$sLabelMsgKey = 'bs-statistics-label-time';
		if( isset($aAvalableGrains[$oDiagram->getActualGrain()]) ) {
			$sLabelMsgKey = $aAvalableGrains[$oDiagram->getActualGrain()];
		}

		$oResponse->payload['label'] = wfMessage( $sLabelMsgKey )->plain();

		$oResponse->success = true;
		return $oResponse;

	}

	protected function isInt( $sValue ) {
		if( is_numeric( $sValue ) && gettype( $sValue + 0) === 'integer') {
			return true;
		}
		return false;
	}
}
