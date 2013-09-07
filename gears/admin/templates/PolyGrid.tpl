<div class="polyGrid">
{! $i = 0}
{! if(empty($in_row)) $in_row = 2}
{foreach $items  as $item}
 {$item}
{if $i%$in_row == 1}<div class="clear"></div>{/if}
{! $i++}
{/foreach} 
</div>
<div class="clear"></div>