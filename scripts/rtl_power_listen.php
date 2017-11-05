#!/usr/bin/php
<?php
//
// Description
// -----------
// This script will launch rtl_power to listen in a frequency range and import
// the dbm values into the database.
//

//
// Initialize QRUQSP by including the qruqsp_api.php
//
$start_time = microtime(true);
global $qruqsp_root;
$qruqsp_root = dirname(__FILE__);
if( !file_exists($qruqsp_root . '/qruqsp-api.ini') ) {
    $qruqsp_root = dirname(dirname(dirname(dirname(__FILE__))));
}

require_once($qruqsp_root . '/qruqsp-mods/core/private/loadMethod.php');
require_once($qruqsp_root . '/qruqsp-mods/core/private/init.php');


//
// Initialize Q
//
$rc = qruqsp_core_init($qruqsp_root, 'json');
if( $rc['stat'] != 'ok' ) {
    print "ERR: Unable to initialize Q\n";
    exit;
}

//
// Setup the $qruqsp variable to hold all things qruqsp.  
//
$q = $rc['q'];

//
// Load required modules
//
qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'dbConnect');
qruqsp_core_loadMethod($q, 'qruqsp', 'core', 'private', 'objectAdd');
qruqsp_core_loadMethod($q, 'qruqsp', 'qsn', 'private', 'rtlpowerProcessLine');

//
// Check which station_id we should use
//
if( isset($q['config']['qruqsp.qsn']['station_id']) ) {
    $station_id = $q['config']['qruqsp.qsn']['station_id'];
} else {
    $station_id = $q['config']['qruqsp.core']['master_station_id'];
}

//
// FIXME: Add rtl_power location to config file
//
$rtl_cmd = 'rtl_power';
$gain = 5;
$start_freq = 144380000;
$end_freq = 144460000;
$bin_size = 5000;

//
// FIXME: Increase frequency range
//
$cmd = "$rtl_cmd -f $start_freq:$end_freq:$bin_size -g $gain -i 10s -P";
//$cmd = 'rtl_power -f 143.5M:148.620M:5k -g 5 -i 10s -P';

$handle = popen($cmd, "r");
$exit = 'no';
$line = '';
$prev_sample = null;
while( $exit == 'no' ) {
    $byte = fread($handle, 1);
    
    if( $byte  == "\n" ) {
        $rc = qruqsp_qsn_rtlpowerProcessLine($q, $station_id, $line, array(
            'gain' => $gain,
            'frequency_start' => $start_freq,
            'frequency_end' => $end_freq,
            'prev_sample' => $prev_sample,
            ));
        if( isset($rc['sample']) ) {
            $prev_sample = $rc['sample'];
        }
        $line = '';
    } else {
        $line .= $byte;
    }

    if( feof($handle) ) {
        break;
    }
}

/*
function processLine($line) {
    $elements = explode(',', $line);
    $date = $elements[0];
    $time = $elements[1];
    $start = $elements[2];
    $step = $elements[4];

    for($i=6;$i<count($elements);$i++) {
        $freq = $start + (($i-6) * $step);
        print $date . ' ' . $time . ' ' . $freq . ' ' . $elements[$i] . "\n";
    }
    print "\n";
}
*/
?>
