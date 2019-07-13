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
function qruqsp_piadmin_sshPasswordSet(&$ciniki) {

    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'newpassword'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'New Password'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'checkAccess');
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.sshPasswordSet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $newpass = $args['newpassword'];
    if( strlen($newpass) < 8 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.5', 'msg'=>'Password must be at least 8 characters.'));
    }

    $hashed_pwd = trim(`echo $newpass | openssl passwd -6 -stdin`);
    if( $hashed_pwd != '' ) {
        `sudo usermod --pass='$hashed_pwd' pi`;
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.6', 'msg'=>'Error trying to set password, this feature does not work on raspbian stretch or below.'));
    }

    return array('stat'=>'ok');
}
?>
