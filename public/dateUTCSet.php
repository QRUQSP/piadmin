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
function qruqsp_piadmin_dateUTCSet(&$ciniki) {

    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        '_date'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Date'),
        '_time'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Time'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'checkAccess');
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.dateUTCSet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $ts = strtotime($args['_date'] . ' ' . $args['_time']);
    if( $ts === false ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.4', 'msg'=>'Invalid date/time format'));
    }

    $update_str = strftime("'%Y-%m-%d %H:%M:%S'", $ts);
    $output = `sudo date -u -s $update_str`;

    return array('stat'=>'ok');
}
?>
