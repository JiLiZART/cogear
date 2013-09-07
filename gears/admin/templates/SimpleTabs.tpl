<div class="tabs">
{if $ajax}
<ul>
 {foreach $tabs as $tab}
 <li><a href="{$tab.link}">{$tab.name}</a></li>
 {/foreach}
</ul>
{else}
{foreach $tabs as $tab}
<h4>{$tab.name}</h4>
<div>
 {$tab.content}
</div>
{/foreach}
{/if}
</div>
 <script type="text/javascript">
  window.addEvent('domready', function(){
    $$('.tabs').each(function(container){
			new SimpleTabs(container, {
				selector: 'h4'
			});
		});
	});
 </script>
