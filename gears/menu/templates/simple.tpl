<ul class="simple-menu">
{foreach $menu.items as $item}
	<li{if $item.active} class="active"{/if}>
		{if $item.image}<img src="{$item.image}" alt="{$item.name}"/>{/if} 
		<a href="{$item.link}">{$item.name}</a>
	</li>
{/foreach}
</ul>