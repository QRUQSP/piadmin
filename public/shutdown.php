<?php
//
// Description
// -----------
// This method will issue a shutdown to the system
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_shutdown(&$ciniki) {

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
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.shutdown');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Issue the shutdown command
    //
    `sudo /bin/systemctl poweroff`;
    exit;

    return array('stat'=>'ok');
}
?>
