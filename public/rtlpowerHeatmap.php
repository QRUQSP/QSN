<?php
//
// Description
// -----------
// This method will return the list of RTL Power Samples for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:              The ID of the tenant to get RTL Power Sample for.
//
function qruqsp_qsn_rtlpowerHeatmap($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_frequency'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Start Frequency'),
        'end_frequency'=>array('required'=>'no', 'blank'=>'no', 'name'=>'End Frequency'),
        'start_date'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Start Date'),
        'start_time'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Start Time'),
        'end_date'=>array('required'=>'no', 'blank'=>'no', 'name'=>'End Date'),
        'end_time'=>array('required'=>'no', 'blank'=>'no', 'name'=>'End Time'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsn', 'private', 'checkAccess');
    $rc = qruqsp_qsn_checkAccess($ciniki, $args['tnid'], 'qruqsp.qsn.rtlpowerHeatmap');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // If no start frequency specified, then find the latest sample
    // FIXME: Check if other arguments specified
    //
    if( !isset($args['start_frequency']) || $args['start_frequency'] == '' ) {
        $strsql = "SELECT sample_date, frequency_start, frequency_end "
            . "FROM qruqsp_qsn_rtlpowersamples "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "ORDER BY sample_date DESC "
            . "LIMIT 1 "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'qruqsp.qsn', 'sample');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsn.10', 'msg'=>'Unable to load sample', 'err'=>$rc['err']));
        }
        if( !isset($rc['sample']) ) {
            $args['start_frequency'] = '';
            $args['end_frequency'] = '';
            $args['start_date'] = '';
            $args['start_time'] = '';
            $args['end_date'] = '';
            $args['end_time'] = '';
        } else {
            $sample = $rc['sample'];
            $args['start_frequency'] = $sample['frequency_start'];
            $args['end_frequency'] = $sample['frequency_end'];
            $dt = new DateTime($sample['sample_date'], new DateTimezone('UTC'));
            $args['end_date'] = $dt->format('Y-m-d');
            $args['end_time'] = $dt->format('H:i:s');
            $dt->sub(new DateInterval('PT10M'));
            $args['start_date'] = $dt->format('Y-m-d');
            $args['start_time'] = $dt->format('H:i:s');
        }
    }
    
    //
    // Heatmap data
    //
    $heatmap = array(
        'start_frequency' => $args['start_frequency'],
        'step' => 5,
        'end_frequency' => $args['end_frequency'],
        'start_date' => $args['start_date'],
        'start_time' => $args['start_time'],
        'end_date' => $args['end_date'],
        'end_time' => $args['end_time'],
        'min' => 0,
        'max' => -9999,
        'data' => array(
            // array('time', 'samples'=>array()),
            ),
        );
    //
    // Setup blank data
    //
    $cur_dt = new DateTime($args['start_date'] . ' ' . $args['start_time'], new DateTimezone('UTC'));
    $start_date_sql = $cur_dt->format('Y-m-d H:i:s');
    $end_dt = new DateTime($args['end_date'] . ' ' . $args['end_time'], new DateTimezone('UTC'));
    $end_date_sql = $end_dt->format('Y-m-d H:i:s');

//    $min_ts = 99999999999;
//    $max_ts = 0;
    $heatmap['xlabels'] = array();
    for($j = $args['start_frequency']; $j <= $args['end_frequency']; $j+=5) {
        $heatmap['xlabels'][] = number_format($j/1000,3);
    }
    while($cur_dt < $end_dt) {
        $slice = array(
            'date' => $cur_dt->format('M j, Y'), 
            'time' => $cur_dt->format('H:i:s'), 
            'samples' => array(),
            );
        $ts = $cur_dt->getTimestamp();
//        if( $ts < $min_ts ) {   
//            $min_ts = $ts;
//        }
//        if( $ts > $max_ts ) {   
//            $max_ts = $ts;
//        }

        for($j = $args['start_frequency']; $j <= $args['end_frequency']; $j+=5) {
            // error_log($ts . ':' . $j . '--' . 0);
            $slice['samples'][$j] = 0;
        }
        $heatmap['data'][$ts] = $slice;

        $cur_dt->add(new DateInterval('PT10S'));
    }
    
    //
    // Get the list of rtlpowersamples
    //
    $strsql = "SELECT CONCAT(s.sample_date, d.frequency) AS sampledataid, "
        . "s.sample_date, "
        . "s.gain, "
        . "s.frequency_start, "
        . "s.frequency_step, "
        . "s.frequency_end, "
        . "s.dbm_lowest, "
        . "s.dbm_highest, "
        . "s.dbm_qty, "
        . "d.frequency, "
        . "d.dbm "
        . "FROM qruqsp_qsn_rtlpowersamples AS s "
        . "LEFT JOIN qruqsp_qsn_rtlpowerdata AS d ON ("
            . "s.id = d.sample_id "
            . "AND d.frequency >= '" . ciniki_core_dbQuote($ciniki, $args['start_frequency']) . "' "
            . "AND d.frequency <= '" . ciniki_core_dbQuote($ciniki, $args['end_frequency']) . "' "
            . ") "
        . "WHERE s.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND s.sample_date >= '" . ciniki_core_dbQuote($ciniki, $start_date_sql) . "' "
        . "AND s.sample_date <= '" . ciniki_core_dbQuote($ciniki, $end_date_sql) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'qruqsp.qsn', array(
        array('container'=>'rtlpowersamples', 'fname'=>'sampledataid', 
            'fields'=>array('sample_date', 'gain', 'frequency_start', 'frequency_step', 'frequency_end', 
                'dbm_lowest', 'dbm_highest', 'dbm_qty', 'dbm', 'frequency')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
//    $min_ts = 999999999999999;
//    $max_ts = 0;
    $utc = new DateTimezone('UTC');
    if( isset($rc['rtlpowersamples']) ) {
        $rtlpowersamples = $rc['rtlpowersamples'];
        foreach($rtlpowersamples as $iid => $rtlpowersample) {
            $dt = new DateTime($rtlpowersample['sample_date'], $utc);
            $i = $dt->getTimestamp();
//            if( $i < $min_ts ) { $min_ts = $i; }
//            if( $i > $max_ts ) { $max_ts = $i; }
            $j = $rtlpowersample['frequency'];
            if( isset($heatmap['data'][$i]['samples'][$j]) ) {
                if( $rtlpowersample['dbm'] > $heatmap['max'] ) {
                    $heatmap['max'] = $rtlpowersample['dbm'];
                }
                if( $rtlpowersample['dbm'] < $heatmap['min'] ) {
                    $heatmap['min'] = $rtlpowersample['dbm'];
                }
                $heatmap['data'][$i]['samples'][$j] = $rtlpowersample['dbm'];
            }
        }
    }

    foreach($heatmap['data'] as $did => $d) {
        $heatmap['data'][$did]['samples'] = array_values($d['samples']);
    }
    $heatmap['data'] = array_values($heatmap['data']);

    return array('stat'=>'ok', 'heatmap'=>$heatmap);
}
?>
