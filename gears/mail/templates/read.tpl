<div class="node inbox" id="pm-{$pm->id}">
	<div  class="title">{$pm->subject}</div>
	<div class="body">{$pm->body}</div>
	<div class="info">
		<span>{? df($pm->created_date)}</span>
		<span>
		<a href="{? l('/user/'.$author->url_name)}"><img class="avatar" src="{$author->avatar.24x24}" width="24" height="24" alt="{$author->name}"></a>
		</span>
		
		<span>
		<a href="{? l('/user/'.$author->url_name)}">
			{$author->name}
		</a>
		</span>
		{if $CI->no_comments}
		<!-- There is no cow level -->
		{else}		
			<a href="/{$CI->gears->mail->url}/read/{$pm->id}/#comments"><img alt="comments" src="/gears/comments/img/icon/comments.png"></a>
			<a class="comments_counter" href="/{$CI->gears->mail->url}/read/{$pm->id}/#comments">{$pm->comments}</a>
			{if $pm->new_comments}
				<a class="new_comments" href="/{$CI->gears->mail->url}/read/{$pm->id}/#comments">+{$pm->new_comments}</a>
			{/if}
		{/if}
		</div>
</div>
{if empty($pm->system)}
<div class="inbox_members">
<b>{_ mail users}</b>
{foreach $users as $user}
		<span id="user-{$user->id}">
		<a href="{? l('/user/'.$user->url_name)}"><img class="avatar" src="{$user->avatar.24x24}" width="24" height="24" alt="{$user->name}"></a> 
		<a href="{? l('/user/'.$user->url_name)}">{$user->name}</a> 
		{if $CI->user->get('id') == $pm->from && $user->id != $pm->from}<a href="#" onclick="kickoutFromInbox({$pm->id},{$user->id}); return false;"><img class="kick" src="/gears/comments/img/icon/destroy.png" width="16" height="16" alt="{_ mail inbox_kickout}" title="{_ mail inbox_kickout}"></a>{/if}
		&nbsp;
		</span>
{/foreach}
</div>
{if $CI->user->get('id') == $pm->from}
<form id="inbox-invite" method="POST">
<div class="field">
	<input type="text" class="text" value="" id="invite" name="invite"/><span class="button"><input class="submit" type="submit" value="{_ mail inbox_invite}"/></span>
	<div class="description">{_ mail inbox_invite_description}</div>
</div>
</form>
{if $CI->gears->buddy}
<script type="text/javascript">
   window.addEvent('domready',function(){
	   autocomplete('invite','/buddy/search/ajax/',{? $CI->user->get('id')});
	   });
 </script>
{/if}
{/if}
{/if}