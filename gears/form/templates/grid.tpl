{if !$elem.data}
	 <p class="bordered centered padding-10">{_form.no_items}</p>
{else}
	<table cellpadding="0" cellspacing="0" [id="{$elem.name}"] border="0" width="100%" {if empty($elem.info.no_class)}class="{$elem.info.class|default:'grid'}"{/if}>
		{if empty($elem.info.no_header)}
			 <thead>
			 <tr>
				 {foreach $elem.header as $title}
				  <td width="{$title[2]}" align="center" valign="middle">{$title[0]}</td>
				 {/foreach}
			 </tr>
			 </thead>
		{/if}
		<tbody>
		  {foreach $elem.data as $dkey=>$item}
		   <tr class="{if $item.class}{$item.class}{elseif $elem.info.tr_class}{$elem.info.tr_class}{else}{if $dkey%2 != 0}even{else}odd{/if}{/if}">
		     {* this var will be used for links *}
			 {! $z = 0}
			 {foreach $elem.header as $key=>$h_item}
			  <td align="{$h_item[4]|default:'center'}" valign="middle">
			  {$h_item.before}
			  {* switch type of row *}
			  {switch $h_item[1]}
			   {case 'dragndrop_tree'}
			   		   <span class="dd" id="{$key}-{$item[$elem.info['primary']]}"  {if $item.style}style="{$item.style}"{/if}><a href="javascript:void(0)" class="dragndrop"><img src="/gears/global/img/blank.gif" width="13" height="13" border="0"/></a><a href="{? l((isset($elem.info.link[$z]) ? $elem.info.link[$z] :$elem.info.link[0]).'/'.(isset($item[$elem.info.link_add[$z]]) ? $item[$elem.info.link_add[$z]]:$item[$elem.info.link_add[0]]))}">{$item[$key]}</a></span>
			   {break}
			   {case 'text'}
				   {$item[$key]}
			   {break}
			   {case 'date'}
				   {$item[$key]|df}
			   {break}
			   {case 'link'}
				   {! $prefix = !empty($elem['info']['link'][$z]) ? $elem['info']['link'][$z] : $elem['info']['link'][0]}
			         {! $suffix = isset($elem['info']['link_add'][$z]) && isset($item[$elem['info']['link_add'][$z]]) ? $item[$elem['info']['link_add'][$z]] : $item[$elem['info']['link_add'][0]]}
				   <a href="{? l($prefix.'/'.$suffix)}">{$item[$key]}</a>
				   {! $z++}
			   {break}
			   {case 'image'}
				   {if $item[$key]}
					   {! $size= empty($elem.info.icon_size) ? '24x24' : $elem.info.icon_size}
					   {if is_array($item[$key])}<a href="{$item[$key]['original']}" target="_blank"><img src="{$item[$key][$size]}" border="0" {if $h_item.class}class="{$h_item.class}"{/if}/></a>
					   {else}<img src="{$item[$key]}" border="0" {if $item.title}title="{$item.title}"{/if} {if $h_item.class}class="{$h_item.class}"{/if}/>
					   {/if}
				   {/if}
			   {break}
			   {case 'checkbox'}
				   {! $show_footer = TRUE}
				   {if !isset($elem.info.undel[$item[$elem.info['primary']]]) OR !in_array($item[$elem.info['primary']],$elem.info.undel)}
				   <input type="checkbox" name="{$key}[{if $elem.info.check_array_name}{$elem.info.check_array_name}{/if}]" value="{$item[$elem.info['primary']]}" {if !empty($item[$key])}checked="checked"{/if}/>
				   {/if}
			   {break}
			   {case 'dragndrop'}
				   <a href="javascript:void(0)" id="{$key}-{$item[$elem.info['primary']]}" class="dragndrop"><img src="/gears/global/img/blank.gif" width="13" height="13" border="0"/></a>
			   {break}
			   {case 'icon'}
				   <a href="{? l((isset($elem.info.link[$z]) ? $elem.info.link[$z] :$elem.info.link[0]).'/'.(isset($item[$elem.info.link_add[$z]]) ? $item[$elem.info.link_add[$z]]:$item[$elem.info.link_add[0]]))}" id="{$key}-{$item[$elem.info['primary']]}"><img src='{$h_item[3]}'/></a>
				   {! $z++}
			   {break}
			  {/switch}
			  {* end switch type of row*}
	  		  {$h_item.after}
			  </td>
			 {/foreach}			
		   </tr>
		  {/foreach}
		  </tbody>
		  {* controls *}
		  {if $show_footer}
		  <tfoot>
		  <tr id="controls">
		   {foreach $elem.header as $key=>$h_item}
			  <td align="center" valign="middle">
			  {if $h_item[1] == 'checkbox' && empty($elem.info.ajax)}
			   <input type="checkbox" onclick="$$('input[name^={$key}').each(function(elem){elem.checked = elem.checked == true ? false : true;})"/><br/>
			   <span class="button"><input type="submit" value="{$h_item[0]}" onclick="if(!$$('input[checked]').length){ msg('{_form.nothing_selected}','{_global.failure}'); return false;} else if(!confirm('{_form.are_you_shure}')){ return false;}"/></span>
			  {/if}	
			  </td>
			 {/foreach}		
		 </tr>	
		 </tfoot>
		 {/if}
	</table>
	{if $elem.info.ajax}
	 
	  <script type="text/javascript">
	  window.addEvent('domready',function(){
		   $$('tr.controls').each(function(tr){tr.setStyle('display','none')});
		   $$("form#{$name} input[type=checkbox]").each(function(el){
			   var state = el.get('checked');
			   el.removeEvents('click').addEvent('click',function(){
				new Request.JSON({
					url: '{$action}',
					data: el.name+'='+el.value,
					onComplete: function(response){
				      if(response){
						if(response.success){
						 msg(response.msg || lang.form.command_success,lang.global.success);
						 {if $elem.info.ajax_delete}
						  el.getParent().getParent().destroy();
						 {/if}
						}
						else {
						msg(response.msg || lang.form.command_failure,lang.global.failure);
						el.set('checked',state);
						}
					}
					else {
						 msg(lang.form.command_failure,lang.global.failure);
						 el.set('checked',state);
					}
				}
				}).post();
			   });

 		   });
	   });
	  </script>
	 
	{/if}
	 {if $elem.info.dragndrop}
	  <script type="text/javascript">
	  window.addEvent('domready',function(){
			var target = $('{$elem.name}').getElement('tbody');
			new Sortables(target,{
			handle: '.dragndrop',
			constrain: true,
			onComplete : function(elem){
				result = new Array();
				target.getElements('.dragndrop').each(function(el){
					result.combine([el.get('id').split('-')[1]]);
				})
				new Request({
					url : document.location.href,
					data : 'result='+result.join(',')
				}).post();
			}
			})
	  });
	  </script>	 
	 {/if}
{if $elem.info.dragndrop_tree_debug}
<script type="text/javascript">

 window.addEvent('domready',function(){
 	var step = 16;
 	$$('.dd').each(function(el,i,all){
 		el.setStyle('position','relative');
 		var tr = el.getParent().getParent();

		var dragndrop = new Drag.Move(el,{
			container: el.getParent(),
			handle: el.getElement('.dragndrop'),
			droppables: tr.getParent().getElements('tr'),
			modifiers: {x:'left',y:null},
			snap: step,
			grid: step,
			onStart: function(el){
				el.store('start',el.getStyle('left').toInt());	
			},
			onDrag: function(el){
				var left = el.getStyle('left').toInt();
				var start = el.retrieve('start');
				var parent = all[i-1];
				if(parent){
					var parent_left = parent.getStyle('left').toInt();
					if(left-step > parent_left){
						el.setStyle('left',parent_left+step);
					}
				}
				else {
					el.setStyle('left',start);
				}
			},
			onComplete: function(el){
				var left = el.getStyle('left').toInt();
				var start = el.retrieve('start');
				var move = left - start;
				move = move > 0 ? step : -step;
				var parent = all[i-1];
				if(parent){
					for(var n = i + 1; n < all.length; n++){
						var next = all[n];
						var next_left = next.getStyle('left').toInt();
						if(next_left <= start) return;
						var next_parent = all[n-1];
						var next_parent_left = next_parent.getStyle('left').toInt();
							next.setStyle('left',next_left+move)
					}
				}
			},
			onEnter: function(el,drop_tr){
				if(drop_tr){
					var start = el.retrieve('start');
					//tr.setProperty('class',drop_tr.hasClass('odd') ? 'odd' : 'even');
					//drop_tr.setProperty('class',drop_tr.hasClass('odd') ? 'even' : 'odd');
					tr.inject(drop_tr,drop_tr.getNext() == tr ? 'before' : 'after');
					for(var n = i + 1; n < all.length; n++){
						var next = all[n];
						var next_left = next.getStyle('left').toInt();
						if(next_left <= start) return;
						next.getParent().getParent().inject(tr,'after')
					}
				}
			},
			onDrop: function(el,drop_tr){
				//if(drop_tr) tr.inject(drop_tr,drop_tr.getNext() == tr ? 'before' : 'after');
			}
		});
 	});
});
	 
	 </script>
 {/if}

	{/if}

