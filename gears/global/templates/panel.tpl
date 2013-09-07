{foreach $elements as $element}
{if $element.before}{$element.before}{/if}
{if $element.type == 'icon'}
{if $element.link}<a href="{$element.link}" {if $element.id}id="{$element.id}"{/if}>{/if}
<img src="{$element.data}" border="0" {if $element.title}title="{$element.title}"{/if} {if $element.class}class="{$element.class}"{/if}/>
{if $element.link}</a>{/if}
{elseif $element.type == 'header'}
<a href="{if $element.link}{$element.link}{else}javascript:void(0){/if}" {if $element.id}id="{$element.id}"{/if}><h1>{$element.text}</h1></a>
{elseif $element.type == 'link'}
<a href="{if $element.link}{$element.link}{else}javascript:void(0){/if}" {if $element.id}id="{$element.id}"{/if}>{$element.text}</a>
{else}
{$element.text}
{/if}
{if $element.after}{$element.after}{/if}
{/foreach}