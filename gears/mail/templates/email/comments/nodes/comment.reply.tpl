Пользователь <a href="{? l('/user/'.$author_url_name)}">{$author}</a> ответил на ваш комментарий к топику <a href="{$link}">{$item->name}</a> следующим текстом:
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
