<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an tutorial.
// This method is typically used by the UI to display a list of changes that have occured
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// tutorial_id:          The ID of the tutorial to get the history for.
// field:                   The field to get the history for.
//
// Returns
// -------
//
function qruqsp_piadmin_gpsCoordsHistory($ciniki) {
    error_log('testing');
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'checkAccess');
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.gpsCoordsHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    if( $args['field'] == 'latitude' ) {
        return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.tenants', 'ciniki_tenant_history', $args['tnid'], 
            'ciniki_tenant_details', 'gps-current-latitude', 'detail_value');
    } elseif( $args['field'] == 'longitude' ) {
        return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.tenants', 'ciniki_tenant_history', $args['tnid'], 
            'ciniki_tenant_details', 'gps-current-longitude', 'detail_value');
    } elseif( $args['field'] == 'altitude' ) {
        return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.tenants', 'ciniki_tenant_history', $args['tnid'], 
            'ciniki_tenant_details', 'gps-current-altitude', 'detail_value');
    }

    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.tenants', 'ciniki_tenant_history', $args['tnid'], 
        'ciniki_tenant_details', $args['field'], 'detail_value');
}
?>
