<form [action="{$action}"] [class="{$class}"] [id="{$name}"] [method="{$method}"][enctype="{$enctype}"]>
{foreach $elements as $elem}
	{*
	============================================
	 If item has template, but it doesn't want to user standart element wrapper
	============================================
	*}
	{if !empty($elem.template) && empty($elem.wrapper)}
		{include file=$elem.template}
	{else}
		{switch $elem.type}
			{case 'div'}
				{if $elem.open}
					<div [id="{$elem.name}"] [class="{$elem.legend}"]>
					{else}
					</div>
				{/if}
			{break}
			
			{case 'title'}
				<h1>{$elem.name}</h1>
			{break}
			{case 'description'}
				<div class="description{if $elem.class} {$elem.class}{/if}">{$elem.name}</div>
			{break}
			{case 'fieldset'}
				{if $elem.open}
					<fieldset[ id="{$elem.name}"][ class="{$elem.class}"]>
					[<legend>{$elem.legend}</legend>]
				{else}
					</fieldset>
				{/if}
			{break}	
			
			{case 'submit'}
			{case 'reset'}
			{case 'button'}
				<span class="button">
					<input id='{$elem.id|default:$elem.name}'[ name="{$elem.name}"] type="{$elem.type}"[ class="{$elem.class}"][ value="{$elem.value}"][ onclick="{$elem.onclick}"][ accesskey="{$elem.accesskey}"]/>
				</span>
			{break}	
			{case 'back'}
				 <a href="javascript: history.go(-1)" id="back-link">{? t('edit back')}</a>
			{break}
			{case 'br'}<br/>
			{break}
			
			{case 'clear'}
				<div class="clear"></div>
			{break}
			
			{default}
			<div class='field'>
				{* Label *}

				{if isset($elem.label) && $elem.label && !isset($elem.label_hidden) && empty($elem.label_after) or $elem.type == 'radio'}
				<label {if in_array($elem.type,explode(',','checkbox'))}class="small"{/if} for="{$elem.id|default:$elem.name}">
					{$elem.label} {if $elem.required}*{/if}
				</label>
					{switch $elem.type}
						{case 'checkbox'}
						{case 'radio'}
						{case 'hidden'}
						{break}
						{case 'input'}
						{case 'text'}
						{case 'password'}
						{if $elem.ajax}<small>(<a href="javascript:void(0)" onclick="update('{$elem.ajax.url}','{if $elem.ajax.update}{$elem.ajax.update}{else}{$elem.id|default:$elem.name}-error{/if}','{$elem.ajax.where}','{$elem.id|default:$elem.name}'{if $elem.ajax.regexp},'{$elem.ajax.regexp}'{/if})">{$elem.ajax.name}</a>)</small>
						{/if}
						{default}
						<br/>
					{/switch}
				{/if}
				{* Field *}
				{if $elem.template}
					{include file=$elem.template}
				{else}
					{switch $elem.type}
						{case 'file'}
						{* file *}
						{if !empty($elem.is_image) && !empty($elem.value) && is_array($elem.value)}
							{if isset($elem.thumbs)}
							 <div class="thumbs">	
								 {foreach $elem.thumbnails as $thumb}
								 <a href="{$elem.value.src}" target="_blank"><img src="{$thumb.src}" width="{$thumb.width}" height="{$thumb.height}" border="0"/></a>
								 {/foreach}
							 </div>
							{else}
								<img src="{$elem.value.src}" width="{$elem.value.width}" height="{$elem.value.height}" border="0"/><br/>
							{/if}
							{elseif !empty($elem.value)}
							<img src="/gears/form/img/file.png"/> <a href="{$elem.value}">{$elem.value}</a>
							<br/>
						{/if}
						 {* input(text) and password *}
						 {case 'input'}
						 {case 'text'}
						 {case 'password'}
						<input id='{$elem.id|default:$elem.name}' name='{$elem.name}' type='{$elem.type}' class="{$elem.type} {if $elem.class}{$elem.class}{/if}" {if isset($elem.value)}value='{$elem.value}'{/if} {if $elem.size}size='{$elem.size}'{/if} {if $elem.onclick}onclick="{$elem.onclick}"{/if} {if $elem.disabled}disabled{/if}/> {if $elem.type == 'file' && !empty($elem.value)}<label><input type="checkbox" onclick="if(this.checked) this.getParent().getPrevious().set('type','hidden').set('value',''); else this.getParent().getPrevious().set('type','file')"/><small>{_ !edit delete}</small></label>{/if}
							{if $elem.autocomplete}
							 <script type="text/javascript">
							   window.addEvent('domready',function(){
								   autocomplete('{$elem.name}','{$elem.autocomplete.url}','{$elem.autocomplete.multiple}');
								   });
							 </script>
							{/if}
							{if $elem.calendar}
							 <script type="text/javascript">
							   window.addEvent('domready',function(){
								   new Calendar({'{$elem.name}':'Y-m-d'});
								   });
							 </script>
							{/if}
						{if $elem.multi}
						<br/>
						<button onclick="if(this.getAllPrevious().filter('input').length > 1) this.getAllPrevious().filter('input').getLast().destroy();">-</button><button onclick="var last = this.getAllPrevious().filter('input').getLast(); var cloned = last.clone(); cloned.injectBefore(this.getPrevious().getPrevious()); cloned.set('id',last.get('id').replace(/\[\d+\]/i,'['+(this.getAllPrevious().filter('input').length)+']')).set('name',last.get('id').replace(/\[\d+\]/i,'['+(this.getAllPrevious().filter('input').length)+']'))">+</button>						
						{/if}
						{break}
						{* textarea *}
						{case 'textarea'}
						<textarea id='{$elem.id|default:$elem.name}' name='{$elem.name}' {if $elem.class}class="{$elem.class}"{/if} {if $elem.rows}rows='{$elem.rows}'{/if} {if $elem.cols}cols='{$elem.cols}'{/if} {if $elem.disabled}disabled{/if}>{if $elem.value}{$elem.value}{/if}</textarea>
						{break}
						
						{* select *}
						{case 'select'}
						  <select id='{$elem.id|default:$elem.name}' name='{$elem.name}{if $elem.multiple}[]{/if}' {if $elem.class}class="{$elem.class}"{/if} {if $elem.multiple}multiple{/if} {if $elem.onchange}onchange='{$elem.onchange}'{/if} {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options as $key=>$val}
							  {if $val == ''}<option></option>
							  {else}
							   <option value='{$key}' {if isset($elem.value) && ((is_array($elem.value) && in_array($key,$elem.value)) OR $key == $elem.value)}SELECTED{/if}>{$val}</option>
							  {/if}
							 {/foreach} 
						 </select>
						{break}
						{* datetime *}
						{case 'datetime'}
						  <div class="datetime">
						  <select id='{$elem.id|default:$elem.name}[day]' name='{$elem.name}[day]' class="day" {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options.day as $key=>$val}
							   <option value='{$key}' {if $key == $elem.value.day}SELECTED{/if}>{$val}</option>
							 {/foreach} 
						 </select>
						  <select id='{$elem.id|default:$elem.name}[month]' name='{$elem.name}[month]' class="month" {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options.month as $key=>$val}
							   <option value='{$key}' {if $key == $elem.value.month}SELECTED{/if}>{$val}</option>
							 {/foreach} 
						 </select>
						  <select id='{$elem.id|default:$elem.name}[year]' name='{$elem.name}[year]' class="year" {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options.year as $key=>$val}
							   <option value='{$key}' {if $key == $elem.value.year}SELECTED{/if}>{$val}</option>
							 {/foreach} 
						 </select>
						  <select id='{$elem.id|default:$elem.name}[hour]' name='{$elem.name}[hour]' class="hour" {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options.hour as $key=>$val}
							   <option value='{$key}' {if $key == $elem.value.hour}SELECTED{/if}>{$val}</option>
							 {/foreach} 
						 </select>
						  <select id='{$elem.id|default:$elem.name}[minute]' name='{$elem.name}[minute]' class="minute" {if $elem.disabled}disabled{/if}>
							 {foreach $elem.options.minute as $key=>$val}
							   <option value='{$key}' {if $key == $elem.value.minute}SELECTED{/if}>{$val}</option>
							 {/foreach} 
						 </select>
						 </div>
						{break}

						{* image_select *}
						{case 'image_select'}
						 <select id='{$elem.id|default:$elem.name}' name='{$elem.name}{if $elem.multiple}[]{/if}' {if $elem.class}class="{$elem.class}"{/if} {if $elem.multiple}multiple{/if} {if $elem.onchange}onchange='{$elem.onchange}'{/if}>
							 {foreach $elem.options as $key=>$val}
							  {if $val == ''}<option></option>
							  {else}
							   <option value='{$key}' {if isset($elem.value) && ((is_array($elem.value) && in_array($key,$elem.value)) OR $key == $elem.value)}SELECTED{/if}>{if is_array($val)}{$val[0]}{else}{$val}{/if}</option>
							  {/if}
							 {/foreach} 
						 </select>
						 {foreach $elem.options. as $key=>$image}
						  {if $image != ''} 
						  <a href="javascript:void(0);" id="{$elem.name}-{$key}" class="selectIcons{if isset($elem.value) && ($key == $elem.value OR (is_array($elem.value) && in_array($key,$elem.value)))}_on{/if}" onclick="selectIcon('{$elem.name}','{$elem.name}-{$key}')"><img src="{if is_array($image)}{$image[1]}{else}{$image}{/if}" border="0" {if is_array($image)}title="{$image[0]}"{/if}/></a>
						  {/if}
						 {/foreach}
						 <div class='clear'></div>
						{break}
						
						{* checkbox *}
						{case 'checkbox'}
						<input id='{$elem.id|default:$elem.name}' name='{$elem.name}' type='{$elem.type}' class="checkbox {if $elem.class}{$elem.class}{/if}" {if !empty($elem.checked) OR !empty($elem.value)}checked="checked"{/if} {if $elem.onclick}onclick="{$elem.onclick}"{/if} {if $elem.disabled} disabled {/if}{if $elem.onchange}onchange='{$elem.onchange}'{/if} {if isset($elem.value)}value='{$elem.value}'{/if}/>
						{break}
						
						{* radio *}
						{case 'radio'}
							{foreach $elem.options as $key=>$val}
								<div>
{if empty($elem.label_after)}
<label class="small" for='{if $elem.id}{$elem.id}{else}{$elem.name}{/if}-item-{$key}'>{$val}</label>{/if}
<input type="{$elem.type}" name="{$elem.id|default:$elem.name}" id="{if $elem.id}{$elem.id}{else}{$elem.name}{/if}-item-{$key}" value="{$key}" class="radio {if $elem.class}{$elem.class}{/if}" {if isset($elem.value) && $key == $elem.value}checked="checked"{/if} {if $elem.onclick}onclick="{$elem.onclick}"{/if} {if $elem.onchange}onchange='{$elem.onchange}'{/if} /> 

{if !empty($elem.label_after)}<label class="small" for='{if $elem.id}{$elem.id}{else}{$elem.name}{/if}-item-{$key}'>{$val}</label>{/if}
</div>
							 {/foreach} 
						{break}
					{/switch}	
				{/if}	
	{if !empty($elem.label) && empty($elem.label_hidden) && !empty($elem.label_after) && $elem.type != 'radio'}<label {if $elem.type == 'checkbox'}class="small"{/if}  for='{$elem.id|default:$elem.name}'>{$elem.label} {if $elem.required}* {/if} </label>{/if}
	{* end template if*}
		{if $elem.description}<div class="description">{$elem.description}</div>{/if}
		<div class="error" id='{$elem.id|default:$elem.name}-error'>{$elem.error}</div>
	</div>
	{if $elem.br}<br/>{/if}
	{break}
	{* default wrapper end*}	
	{case 'hidden'}
	<input id='{$elem.id|default:$elem.name}' name='{$elem.name}' type='{$elem.type}' class='hidden' value='{$elem.value}'/>
	{break}		
		</div>
		{/switch}
	{/if}
{/foreach}
</form>

{if $js_validation}
		<script type="text/javascript">
		formCheck = new FormCheck('{$id|default:$name}',{
		msg: {$errors_msg},
		display : {
			errorsLocation: (Browser.Engine.version < 5 && Browser.Engine.trident) ? 3 : {$errors_location}
		}
		});
		</script>		
	
{/if}
{if $md5}
<script type="text/javascript">
	$('{$id|default:$name}').addEvent('submit', function()  {
		if(this.isValid && this.isValid === false) {
			return false;
		}
		else {
			this.getElements(".md5").each(function(elem){
				var hidden = new Element('input').set('type','hidden').set('name',elem.id).set('id',elem.name+'_md5').set('value',$chk(elem.value) ? hex_md5(hex_md5(elem.value)) : elem.value).inject(elem,'after');
				elem.removeProperty('name');
			});
		 }
		});
	
</script>
{/if}
{if $via_cookie}
<script type="text/javascript">
	$('{$id|default:$name}').addEvent('submit', function()  {
		if($defined(this.isValid) && !this.isValid) {
			return false;
		}
		else {
			this.getElements(".via_cookie").each(function(elem){
			    if(elem.get('value')) var value = elem.get('value');
			    if($(elem.id+'_md5')) var value = $(elem.id+'_md5').get('value');
			    if(value) Cookie.write(elem.id,value,{domain: "."+url, path: "/"});
			    elem.removeProperty('name');
			});
		 }
		});
</script>
{/if}	
