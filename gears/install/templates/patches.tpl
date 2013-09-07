<div class="notice">
{? t('install patch_found')}
<ul>
{foreach $gears as $gear}
	<li>{? t('gears '.$gear)}</li>
{/foreach}
</ul>
{? button('install patch','/admin/install/patch/',TRUE)}
</div>
