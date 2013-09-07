<div id="comment-{$comment.id}" class="comment{if $comment.class} {$comment.class}{/if}" {if !empty($type) && $type =='tree' && !empty($comment.level)}style="margin-left: {? $comment.level*3 > 20 ? 21 : $comment.level*3}em;"{/if}>
	 {$comment.before}
	 <div class="header">{$comment.header}</div>
	 <div class="body">{$comment.body}</div>
	 <div class="extra">{$comment.extra}</div>
	 {$comment.after}
</div>	 
