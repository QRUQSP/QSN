<?php
//
// Description
// -----------
// This function will process and inject the rtl power sample with multiple data points.
//
// Arguments
// ---------
// q:
// station_id:                  The ID of the station to check the session user against.
// method:                      The requested method.
//
function qruqsp_qsn_rtlpowerProcessLine(&$q, $station_id, $line, $args) {
  
    //
    // Setup the sample
    //
    $elements = explode(',', $line);

    //
    // Date will be in local timezone, convert to UTC
    //
    $dt = new DateTime($elements[0] . ' ' . $elements[1]);
    $dt->setTimezone(new DateTimezone('UTC'));
    $sample = array(
        'id' => 0,
        'sample_date' => $dt->format('Y-m-d H:i:s'),
        'gain' => $args['gain'],
        'frequency_start' => $args['frequency_start']/1000,
        'frequency_step' => $elements[4]/1000,
        'frequency_end' => $args['frequency_end']/1000,
        );

    //
    // Check if date and time is the same as the previous sample
    //
    if( isset($args['prev_sample']['sample_date']) && $args['prev_sample']['sample_date'] == $sample['sample_date'] ) {
        $sample = $args['prev_sample'];
    } else {
        $rc = qruqsp_core_objectAdd($q, $station_id, 'qruqsp.qsn.rtlpowersample', $sample);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $sample['id'] = $rc['id'];
    }

    //
    // Setup the insert query
    //
    $rc = qruqsp_core_dbConnect($q, 'qruqsp.qsn');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $dh = $rc['dh'];

    $stmt = mysqli_prepare($dh, "INSERT INTO qruqsp_qsn_rtlpowerdata (sample_id, frequency, dbm) VALUES ("
        . "'" . qruqsp_core_dbQuote($q, $sample['id']) . "', ?, ?);");
    if( $stmt === false ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsn.4', 'msg'=>'Unable to setup insert'));
    }
    $freq = 0;
    $dbm = 0;
    $stmt->bind_param('ii', $freq, $dbm);

    $start = $elements[2]; 
    $step = $elements[4];

    for($i=6;$i<count($elements);$i++) {
        $freq = (int)($start + (($i-6) * $step))/1000;
        $dbm = (int)($elements[$i] * 100);
        $rc = $stmt->execute();
        if( $rc === false ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.qsn.3', 'msg'=>'Unable to add data'));
        }
    }
    
    return array('stat'=>'ok', 'sample'=>$sample);
}
?>
