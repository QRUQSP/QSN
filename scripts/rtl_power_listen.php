#!/usr/bin/php
<?php
//
// Description
// -----------
// This script will launch rtl_power to listen in a frequency range and import
// the dbm values into the database.
//

//
// Initialize CINIKI by including the ciniki-api.ini
//
$start_time = microtime(true);
global $ciniki_root;
$ciniki_root = dirname(__FILE__);
if( !file_exists($ciniki_root . '/ciniki-api.ini') ) {
    $ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
}

require_once($ciniki_root . '/ciniki-mods/core/private/loadMethod.php');
require_once($ciniki_root . '/ciniki-mods/core/private/init.php');


//
// Initialize Q
//
$rc = ciniki_core_init($ciniki_root, 'json');
if( $rc['stat'] != 'ok' ) {
    print "ERR: Unable to initialize Q\n";
    exit;
}

//
// Setup the $ciniki variable to hold all things qruqsp.  
//
$ciniki = $rc['ciniki'];

//
// Load required modules
//
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbConnect');
ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
ciniki_core_loadMethod($ciniki, 'qruqsp', 'qsn', 'private', 'rtlpowerProcessLine');

//
// Check which tnid we should use
//
if( isset($ciniki['config']['qruqsp.qsn']['tnid']) ) {
    $tnid = $ciniki['config']['qruqsp.qsn']['tnid'];
} else {
    $tnid = $ciniki['config']['ciniki.core']['master_tnid'];
}

//
// FIXME: Add rtl_power location to config file
//
$rtl_cmd = 'rtl_power';
$gain = 5;
//$start_freq = 144380000;
//$end_freq = 144460000;
$start_freq = 143500000;
$end_freq = 148620000;
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
        $rc = qruqsp_qsn_rtlpowerProcessLine($ciniki, $tnid, $line, array(
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
?>
