

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
	<title><?php if(isset($meta['title'])){ echo $meta['title'];} ?></title>
<meta content="text/html; charset=<?php echo  isset($CI->site->encoding) ? $CI->site->encoding : 'utf-8';?>" http-equiv="Content-Type">
<link rel="shortcut icon" href="http://<?php if(isset($CI->site->url)){ echo $CI->site->url;} ?>/favicon.ico" />
<?php if(isset($meta['info'])){ echo $meta['info'];} ?>
<?php if(isset($css)){ echo $css;} ?>
<?php if(isset($scripts)){ echo $scripts;} ?>
<?php if(isset($extra)){ echo $extra;} ?>
<script type="text/javascript">
url = 'http://<?php if(isset($CI->uri->url)){ echo $CI->uri->url;} ?><?php if(isset($CI->uri->subdir) && $CI->uri->subdir):?>/<?php if(isset($CI->uri->subdir)){ echo $CI->uri->subdir;} ?>/<?php endif; ?>';
</script>
</head>

<body>
<div id="header">
  <a href="http://<?php if(isset($meta['url'])){ echo $meta['url'];} ?>/"><img src="/templates/default/img/logo.gif" border="0" align="left" alt="<?php if(isset($meta['title'])){ echo $meta['title'];} ?>" width="305" height="95" id="logo"/></a>
  <?php if(isset($cpanel)){ echo $cpanel;} ?>
 </div>
 <?php if(isset($menu['main'])){ echo $menu['main'];} ?>
<div id="wrapper">
	<div id="content">
