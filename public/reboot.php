<?php
//
// Description
// -----------
// This method will issue a reboot to the 
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_reboot(&$ciniki) {

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
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.reboot');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Issue the reboot command
    //
    `sudo /bin/systemctl reboot`;
    exit;

    return array('stat'=>'ok');
}
?>
