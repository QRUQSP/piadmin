<?php
//
// Description
// -----------
// This method returns the current UTC date and time of the system
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_dateUTCGet(&$ciniki) {

    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'checkAccess');
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.dateUTCGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current date and time
    //
    $dt = new DateTime('now', new DateTimezone('UTC'));

    return array('stat'=>'ok', '_date'=>$dt->format('Y-m-d'), '_time'=>$dt->format('H:i:s'));
}
?>
