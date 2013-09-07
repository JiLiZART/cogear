Пользователь <a href="{? l('/user/'.$author_url_name)}">{$author}</a> написал комментарий к письму "<a href="{$link}">{$item->subject}</a>":
<p><i>
{$body}
</i></p>
{if $original->body}
Сообщение, на которое ответил пользователь:
<p>
<i>
{$original->body}
</i>
</p>
{/if}

<a href="{$link}">Ответить &rarr;</a>
