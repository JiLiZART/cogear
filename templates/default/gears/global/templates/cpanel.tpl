{if !$CI->user->is_logged}
<div class="hidden">
<div id="login-form">
<form method="post" id="quick-login" action="http://{$CI->site->url}/user/login/">
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
		<a href="http://{$CI->site->url}/user/lostpassword/">Забыли пароль?</a>
		<a href="http://{$CI->site->url}/user/register/">Регистрация</a>
		</div>
					<span class="button">
					<input type="submit" onclick="$('quick-login').getElement('input[name=action]').set('value',this.name);" value="Войти" name="submit" id="submit">
					</span>
				
			</form>
			<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
<iframe src="https://loginza.ru/api/widget?overlay=loginza&token_url=http://{$CI->site->url}/loginza/" 
style="width:359px;height:190px;" scrolling="no" frameborder="no"></iframe>

</div>
</div>
{/if}
<ul id="cpanel"{if $CI->user->is_logged} class="authorized"{/if}>
{if $CI->user->is_logged}
{foreach $elements as $elem}
<li [id="{$elem.id}" ][class="{$elem.class}"] width="{! if(isset($elem.width)) $elem.width = str_replace('%','',$elem.width)}{$elem.width|default:'10'|}%" {if $elem.align}align="{$elem.align}"{/if}>
{if $elem.link}<a href="{$elem.link}" class="no-decoration">{/if}
{if $elem.type == 'icon'}
 <img src="{$elem.data}" {if $elem.data_class}class="{$elem.data_class}"{/if}/>
{else}
<span class="cpanel-number">{$elem.data}</span>
{/if}
{if $elem.link}</a>{/if}
{if $elem.link}<a href="{$elem.link}">{/if}
{$elem.text}
{if $elem.link}</a>{/if}
</li>
{/foreach}
{else}
<li><a href="{? l('/user/login/')}" onclick="loader.elem('login-form',650,490);return false;">войти</a></li>
<li><a href="{? l('/user/register/')}">зарегистрироваться</a></li>
{/if}
</ul>