<div id="options" class="alpha">
	<div id="options-wrapper">
			<ul>
				<li class="corners"><img src="/templates/cogear/img//options/dock.left.png" id="dock-left" width="22" height="18" border="0"  alt="леводок"/></li>
				{foreach from=$elements item=$elem}
				<li><a href="{$elem.link}" {if $elem.class}class="{$elem.class}"{/if}><img src="{$elem.src}" width="{$elem.width}" height="{$elem.height}" border="0" alt="{$elem.alt}"/></a></li>
				{/foreach}
				<li class="corners"><img src="/templates/cogear/img//options/dock.right.png" id="dock-right" width="22" height="18" border="0"  alt="праводок"/></li>
			</ul>
	</div>
</div>