<table[ id="{$id}"] class="tabs" width="100%" height="32" cellpadding="0" cellspacing="0">
<tr>
<td class="first">&nbsp;</td>
{foreach $elements as $element}
<td {if $element.name}id="{$element.name}"{/if} {if $element.class}class="{$element.class}"{/if} width="{if $element.width}{$element.width}{else}{? 100/count($elements)}%{/if}"><a href="{$element.link}">{$element.text}</a></td>
{/foreach}
<td class="last">&nbsp;</td>
</tr>
</table>