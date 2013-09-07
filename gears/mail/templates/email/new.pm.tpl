Привет, <strong>{$user}</strong>!
<p>
Пользователь <a href="{? l('/user/'.$author_url)}">{$author}</a> отправил вам личное сообщение с темой &laquo;<strong>{$subject}</strong>&raquo;.
</p>
<p>
<a href="{? l('/'.$CI->gears->mail->url.'/view/'.$id)}">Прочитать &darr;</a>
</p>