<form action="/{$CI->gears->mail->url}/" id="inbox-search" method="GET">
<div class="field">
	{! $query = $CI->input->get('query')}
	<input type="text" class="text" value="{$query}" id="query" name="query"/>
	{if !$query}<span class="button"><input type="submit" value="{_ mail search}" /></span>{/if}
	{if $query}<a href="/{$CI->gears->mail->url}/" class="button">{_ mail search_reset}</a>{/if}
</div>
</form>
<div class="clear"></div>