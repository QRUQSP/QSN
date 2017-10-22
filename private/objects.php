<?php
//
// Description
// -----------
// This function returns the list of objects for the module.
//
// Arguments
// ---------
//
function qruqsp_qsn_objects(&$q) {
    //
    // Build the objects
    //
    $objects = array();
    $objects['rtlpowersample'] = array(
        'name'=>'RTL Power Sample',
        'o_name'=>'rtlpowersample',
        'o_container'=>'rtlpowersamples',
        'sync'=>'yes',
        'table'=>'qruqsp_qsn_rtlpowersamples',
        'fields'=>array(
            'sample_date'=>array('name'=>'Sample UTC Date'),
            'gain'=>array('name'=>'Gain'),
            'frequency_start'=>array('name'=>'Start Frequency'),
            'frequency_step'=>array('name'=>'Step Frequency'),
            'frequency_end'=>array('name'=>'End Frequency'),
            'dbm_lowest'=>array('name'=>'Lowest DB', 'default'=>'0'),
            'dbm_highest'=>array('name'=>'Highest DB', 'default'=>'0'),
            'dbm_qty'=>array('name'=>'Number of Data Points', 'default'=>'0'),
            ),
        'history_table'=>'qruqsp_qsn_history',
        );
    //
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
