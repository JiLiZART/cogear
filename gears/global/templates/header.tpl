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
<body>