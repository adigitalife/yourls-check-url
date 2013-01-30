<?php
/*
Plugin Name: Check URL
Plugin URI: http://code.google.com/p/yourls-check-url/
Description: This plugin checks the reachability of an entered URL before creating the short link for it. An error is then returned if the entered URL is unreachable.
Version: 1.1
Author: Aylwin
Author URI: http://adigitalife.net/
*/

// Hook our custom function into the 'shunt_add_new_link' filter
yourls_add_filter( 'shunt_add_new_link', 'churl_reachability' );

// Add a new link in the DB, either with custom keyword, or find one
function churl_reachability( $churl_reachable, $url, $keyword = '' ) {
	global $ydb;

    // Check if the long URL is a different type of link
    $different_urls = array (
        array ( 'mailto://', 9 ),
        array ( 'ftp://', 6 ),
        array ( 'javascript://', 13),
        array ( 'file://', 7 ),
        array ( 'telnet://', 9),
        array ( 'ssh://', 6),
        array ( 'sip://', 6)
        );

    foreach ($different_urls as $url_type){
        if (substr( $url, 0, $url_type[1] ) == $url_type[0]){
            $churl_reachable = true; // No need to check reachability if URL type is different
            break;
        }
    }
	
	// Check if the long URL is a mailto
	if ($churl_reachable == false){
		$churl_reachable = churl_url_exists( $url );  // To do: figure out how to use yourls_get_remote_content( $url ) instead.
	}
		
	// Return error if the entered URL is unreachable
	if ( $churl_reachable == false ){
		$return['status']   = 'fail';
		$return['code']     = 'error:url';
		$return['message']  = 'The entered URL is unreachable.  Check the URL or try again later.';
		$return['statusCode'] = 200; // regardless of result, this is still a valid request
		return yourls_apply_filter( 'add_new_link_fail_unreachable', $return, $url, $keyword, $title );
	} else {
		return false;
	}
}

function churl_url_exists( $churl ){
	$handle = @fopen($churl, "r");
	if ($handle === false)
	return false;
	fclose($handle);
	return true;
} 