{* Content closed*}
{if empty($disabled)}
</div>
<div id="sidebar">
{foreach $widgets as $widget}
 <div {if $widget.config.nobg}style="background:none;"{/if} class="widget{if $widget.config.class} {$widget.config.class}{/if}" {if $widget.config.id}id="{$widget.config.id}"{/if}>
	 <h1>{if !empty($widget.config.title)}{$widget.config.title}{else}{$widget.title}{/if}</h1>
 	 {$widget.content}
 </div>
{/foreach}
</div>
<style>
#content {
	width: 66%;
}
#sidebar{
	float:left;
	width:29%;
	margin-left: 2%;
}
</style>
<div class='clear'>
{/if}
{* No need to close sidebar div - %footer template will automatically do this *}