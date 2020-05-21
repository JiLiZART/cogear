<?php
/**
* Get user agent
*
* @return	object
*/
function user_agent(){
    $agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;
    $browser = 'unknown';
    $version = 0;

    switch(TRUE){
	    case strpos($agent,'Safari'):
		    $browser = 'safari';
		    if(preg_match('/Version\/([\d\.]+)/',$agent,$matches)){
			    $version = $matches[1];
		    }
	    break;
	    case strpos($agent,'MSIE'):
		    $browser = 'ie';
		    if(preg_match('/MSIE\s([\d\.]+)/',$agent,$matches)){
			    $version = $matches[1];
		    }
	    break;
	    case strpos($agent,'Firefox'):
		    $browser = 'firefox';
		    if(preg_match('/Firefox\/([\d\.]+)/',$agent,$matches)){
			    $version = $matches[1];
		    }
	    break;
	    case strpos($agent,'Opera') !== FALSE:
		    $browser = 'opera';
		    if(preg_match('/Opera\/([\d\.]+)/',$agent,$matches)){
			    $version = $matches[1];
		    }
	    break;
	    case strpos($agent,'Chrome'):
		    $browser = 'chrome';
		    if(preg_match('/Version\/([\d\.]+)/',$agent,$matches)){
			    $version = $matches[1];
		    }
	    break;
    }
    return array('browser'=>$browser,'version'=>$version);
}