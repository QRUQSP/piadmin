<?php
//
// Description
// -----------
// This function will check to make sure a command is loaded into cron.
// It will not confirm if it's commented out, as that may be required in some instances.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_hooks_cronAdd(&$ciniki, $tnid, $args) {

    //
    // Check to make sure piadmin is active for this tenant
    //
    if( !isset($ciniki['tenant']['modules']['qruqsp.piadmin']) ) {
        return array('stat'=>'disabled', 'err'=>array('code'=>'qruqsp.piadmin.16', 'msg'=>'Crontab editing not allowed.'));
    }
    
    if( !isset($args['minute']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.8', 'msg'=>'No minute specified for adding to crontab.'));
    }
    if( !isset($args['hour']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.9', 'msg'=>'No hour specified for adding to crontab.'));
    }
    if( !isset($args['day']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.10', 'msg'=>'No day specified for adding to crontab.'));
    }
    if( !isset($args['month']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.11', 'msg'=>'No month specified for adding to crontab.'));
    }
    if( !isset($args['weekday']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.12', 'msg'=>'No weekday specified for adding to crontab.'));
    }
    if( !isset($args['cmd']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.13', 'msg'=>'No command specified for adding to crontab.'));
    }
    if( !isset($args['log']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.14', 'msg'=>'No log specified for adding to crontab.'));
    }

    //
    // Get lines of the crontab
    //
    exec("crontab -l", $lines);
    foreach($lines as $line) {
        if( preg_match("#{$args['cmd']}#", $line) ) {
            return array('stat'=>'exists');
        }
    }

    //
    // Add the line for the entry
    //
    $lines[] = $args['minute'] . ' ' . $args['hour'] . ' ' . $args['day'] . ' ' . $args['month'] . ' ' . $args['weekday']
        . ' ' . $args['cmd'] . ' >> ' . $args['log'] . ' 2>&1';
    
    //
    // Output the lines to temp file
    //
    $rc = file_put_contents('/tmp/crontab', implode("\n", $lines) . "\n");
    if( $rc === false ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.15', 'msg'=>'Could not write new crontab'));
    }

    //
    // Load new crontab
    //
    $rc = exec("crontab /tmp/crontab 2>&1 ", $result);
    if( $rc != '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.17', 'msg'=>$rc));
    } else {
        unlink("/tmp/crontab");
    }

    return array('stat'=>'ok');
}
?>
