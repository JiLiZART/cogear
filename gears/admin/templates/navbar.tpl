<div class="navbar">
{foreach $navbar as $key=>$item}
 {if $key!=(count($navbar)-1)}<a href="{$item.link}">{$item.name}</a> &rarr;
 {else}{$item.name}
 {/if}
{/foreach}
</div>