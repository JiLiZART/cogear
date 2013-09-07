<div class="voting vote-{$type}">
	<div class="vote-up{if $voted !== FALSE && $voted <= 0 OR $is_owner OR $period} passive{/if}"><a id="vote-up-{$type}-{$id}" href="javascript:void(0)" onclick="return vote('up','{$type}','{$id}')"></a></div>
	{if $voted !== FALSE}
		<div class="votes{if $votes > 0} good{elseif $votes == 0} zero{else} bad{/if}" id="votes-{$type}-{$id}">{$votes}</div>
	{else}
		{if $show_points OR $is_owner}
		<div class="votes{if $votes > 0} good{elseif $votes == 0} zero{else} bad{/if}" id="votes-{$type}-{$id}">{$votes}</div>
		{else}
		<div class="votes{if $votes > 0} good{elseif $votes == 0} zero{else} bad{/if}" id="votes-{$type}-{$id}"><a onclick="return vote('zero','{$type}','{$id}')" title="{? t('points show_down')}"></a></div>
		{/if}
	{/if}
	<div class="vote-down{if $voted !== FALSE && $voted >= 0 OR $is_owner OR $period} passive{/if}"><a id="vote-down-{$type}-{$id}" href="javascript:void(0)" onclick="return vote('down','{$type}','{$id}')"></a></div>
	{if $show_votes}
		<div class="points_counter" id="points-counter-{$type}-{$id}">{? t('points points_counter',$points_counter)}</div>
	{/if}
</div>
