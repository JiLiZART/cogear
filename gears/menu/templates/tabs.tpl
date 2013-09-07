<table class="tabs" width="100%" height="32" cellpadding="0" cellspacing="0">
<tr>
<td class="first">&nbsp;</td>
{foreach $menu.items as $item}
<td {if $item.url_name}id="{$item.url_name}"{/if} width="{? 100/count($menu.items)}%" {if $item.active}class="active"{/if}><a href="{$item.link}">{$item.name}</a></td>
{/foreach}
<td class="last">&nbsp;</td>
</tr>
</table>