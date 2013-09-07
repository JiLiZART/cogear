{* Content closed*}
{if empty($disabled)}
<div id="sidebar" class="grid_4 omega">
{foreach $widgets as $widget}
 <div {if $widget.config.nobg}style="background:none;"{/if} class="widget{if $widget.config.class} {$widget.config.class}{/if}" {if $widget.config.id}id="{$widget.config.id}"{/if}>
	 {if !isset($widget.config.notitle)}<div class="widget-header"><h3>{if !empty($widget.config.title)}{$widget.config.title}{else}{$widget.title}{/if}</h3></div>{/if}
 	 <div class="widget-content">{$widget.content}</div>
 </div>
{/foreach}
</div>
{/if}
{* No need to close sidebar div - %footer template will automatically do this *}