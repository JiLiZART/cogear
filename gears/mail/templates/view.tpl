<div class="mail">
<span class="header">{$subject}</span>
<span class="author"> <img src="{$avatar.24x24}" class="avatar" hspace="5"/>
<a href="{? l('/user/'.$url_name)}">{$name}</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$created_date|df}</span>
<div class="body">{$body}</div>
</div>
