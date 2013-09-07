<div class="field">
	{if $elem.label}<label for='{if $elem.id}{$elem.id}{else}{$elem.name}{/if}'>{$elem.label}</label> {if $elem.required}* {/if}<small>(<a href="javascript:void(0)" onclick="update('/captcha/','captcha-image','bottom')">{_edit update}</a>)</small><br/>{/if}
	<div id="captcha-image" class="fc-error">{$elem.image}</div>
	<input id='{if $elem.id}{$elem.id}{else}{$elem.name}{/if}' name='{$elem.name}' type='text' class="text {if $elem.class}{$elem.class}{/if}" {if $elem.value}value='{$elem.value}'{/if} {if $elem.size}size='{$elem.size}'{/if} {if $elem.onclick}onclick="{$elem.onclick}"{/if}/>
	{if $elem.description}<div class="description">{$elem.description}</div>{/if}
	{if $elem.error}<div class="error">{$elem.error}</div>{/if}
</div>
