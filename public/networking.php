<?php
//
// Description
// -----------
// This method will return the current network details for the Pi.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_networking(&$ciniki) {
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
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.networking');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    $rsp = array('stat'=>'ok', 'interfaces'=>array());

    exec('ifconfig', $lines);
    $interface = '';
    $ip = '';
    $netmask = '';
    $mac = '';
    foreach($lines as $line) {
        if( preg_match("/^([^\s]*): /", $line, $m) ) {
            $interface = $m[1];
            $ip = '';
            $netmask = '';
            $mac = '';
        }
        elseif( preg_match("/^\s+inet ([0-9\.]+)\s+netmask ([0-9\.]+)\s+broadcast ([0-9\.]+)/", $line, $m) ) {
            $ip = $m[1];
            $netmask = $m[2];
        }
        elseif( preg_match("/^\s+ether ([0-9a-fA-F\:]+) /", $line, $m) ) {
            $mac = $m[1];
        }
        elseif( $line == '' && $ip != '' ) {
            $rsp['interfaces'][] = array(
                'name' => $interface,
                'ip' => $ip,
                'netmask' => $netmask,
                'mac' => $mac,
                );
        }
    }

    return $rsp;
}
?>
