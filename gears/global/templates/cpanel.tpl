<table cellpadding="0" cellspacing="5" id="cpanel" class="cpanel" width="50%">
<tr>
{foreach $elements as $elem}
<td [id="{$elem.id}" ][class="{$elem.class}"] width="{! if(isset($elem.width)) $elem.width = str_replace('%','',$elem.width)}{$elem.width|default:'10'|}%" {if $elem.align}align="{$elem.align}"{/if} valign="bottom">
{if $elem.link}<a href="{$elem.link}" {if $elem.onclick}onclick="{$elem.onclick}"{/if} class="no-decoration">{/if}
{if $elem.type == 'icon'}
 <img src="{$elem.data}" {if $elem.data_class}class="{$elem.data_class}"{/if}/>
{else}
<span class="cpanel-number">{$elem.data}</span>
{/if}
{if $elem.link}</a>{/if}
<br/>
{if $elem.link}<a href="{$elem.link}">{/if}
{$elem.text}
{if $elem.link}</a>{/if}
</td>
{/foreach}
</tr>
</table>
{if !$CI->user->is_logged}
<div class="hidden">
<div id="login-form">
<form method="post" id="quick-login" action="/user/login/">
		<div class="field">
		<label for="login">
			Электропочта или логин *				
		</label><br/>
		<input type="text" class="text validate['required']" name="login" id="login"> 																											
		</div>
			
		<div class="field">
		<label for="password">
			Пароль *				
		</label><br/>
		<input type="password" size="25" class="password validate['required']" name="password" id="password"> 																						</div>
		<div class="field">
		<label for="save_cookies" class="small">
			Запомнить? 				
		</label>
		<input type="checkbox" class="checkbox " name="save_cookies" id="save_cookies">
		&nbsp;&nbsp;&nbsp;
		<a href="/user/lostpassword/">Забыли пароль?</a>
		&nbsp;&nbsp;&nbsp;
		<a href="/user/register/">Регистрация</a>
		&nbsp;&nbsp;&nbsp;
		<a href="/user/openid/"><img height="32" width="32" align="absmiddle" alt="OpenID" src="/gears/user/img/openid_small.png"></a>
		</div>
			
					<span class="button">
					<input type="submit" onclick="$('quick-login').getElement('input[name=action]').set('value',this.name);" value="Войти" name="submit" id="submit">
					</span>
				
			</form>
</div>
</div>
{/if}
