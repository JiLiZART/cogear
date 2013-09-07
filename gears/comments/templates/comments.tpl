{if $wrapper}<div id="comments">{/if}
	{foreach $comments as $comment}
	 {include file="comments comment.tpl"}
	{/foreach}
{if $wrapper}</div>{/if}