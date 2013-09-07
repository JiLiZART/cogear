{*
 You can use
 {include file="global header"}
 to include default site header
*}
{* header *}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
	<title>{$meta.title}</title>
<meta content="text/html; charset={$CI->site->encoding|default:'utf-8'}" http-equiv="Content-Type">
<link rel="shortcut icon" href="http://{$CI->site->url}/favicon.ico" />
{$meta.info}
{$css}
{$scripts}
{$extra}
<script type="text/javascript">
url = 'http://{$CI->uri->url}{if $CI->uri->subdir}/{$CI->uri->subdir}/{/if}';
</script>
</head>
{* /header *}
<body>
<div id="cpanel-holder">{$cpanel}</div>
<div class="container_12">
<div id="header" class="grid_12">
  <div class="grid_4 alpha">
  <a href="http://{$meta.url}/"><img src="{$tpl}/images/logo.png" border="0" align="left" alt="{$meta.title}" width="300" height="95" id="logo"/></a>
  </div>
  <div class="grid_8 omega">
  </div>
 </div>
[<div class="grid_12 alpha omega">{$menu.main}</div>]
<div id="wrapper" class="grid_12">
{if $twitter}
<div id="twitter">
						<div id="birdy"><a href="http://twitter.com/{$CI->gears->twitter->login}"><img src="{$tpl}images/twitter.png" alt="Twitter" width="48" height="48"/></a></div>
						<div id="tweet">
							<div id="tweet-open">&nbsp;</div>
							<div id="tweet-body">{$twitter}</div>
							<div id="tweet-close">&nbsp;</div>
						</div>
					</div>
{/if}
<div id="content" class="grid_{if $sidebar}8{else}12 omega{/if} alpha">
