<div class="drag_list">
	{$elem.title}
	{if $elem.target}
	<ul>
	{if $elem.items}
	{foreach $elem.items  as $item}
		 <li {if $item.class}class="{$item.class}"{/if}>{$item.name}</li>
	{/foreach}
	{/if}
	</ul>
	<ul {if $elem.name}id="{$elem.name}"{/if} {if $elem.class}class="{$elem.class}"{/if}>
	{if $elem.additems}
	{foreach $elem.additems as $item}
		 <li {if $item.id}id="{if $elem.name}{$elem.name}-{/if}{$item.id}{/if}" {if $item.class}class="{$item.class}"{/if}>{$item.name}</li>
	{/foreach}
	{/if}
 	<li class="undraggable"></li>
	</ul>
	{else}
	<ul {if $elem.name}id="{$elem.name}"{/if} {if $elem.class}class="{$elem.class}"{/if}>
	{if $elem.items}
		{foreach $elem.items as $item}
		 <li {if $item.id}id="{if $elem.name}{$elem.name}-{/if}{$item.id}{/if}" {if $item.class}class="{$item.class}"{/if}>{$item.name}</li>
		{/foreach}
	{/if}
	<li class="undraggable"></li>
	</ul>
	{/if}
</div>	
