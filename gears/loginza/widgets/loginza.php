<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function loginza_widget($CI,$config)
{
	$return_url='http://'.$CI->uri->url.'/loginza';
	$widget='<script src="https://s3-eu-west-1.amazonaws.com/s1.loginza.ru/js/widget.js" type="text/javascript"></script>
<a href="https://loginza.ru/api/widget?token_url='.$return_url.'" class="loginza">Войти через OpenID</a>';

	if(!$CI->user->is_logged())
	{
		return $widget;
	}
	else
	{
		$widget="<a href=\"http://".$CI->uri->url."/user/logout\">Logout</a>";
	}
	return $widget;
}
