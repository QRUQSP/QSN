<?php
//
// Description
// -----------
// This function returns the int to text mappings for the module.
//
// Arguments
// ---------
// q: 
//
function qruqsp_qsn_maps(&$ciniki) {
    //
    // Build the maps object
    //
    $maps = array();
    $maps['object'] = array('field'=>array(
        'int'=>'text',
    ));

    //
    // Return the maps array
    //
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
