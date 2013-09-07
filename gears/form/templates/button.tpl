{if $br == 'before'}<br/>{/if}
<span class="button{if $class} {$class}{/if}"><input type="button" value="{$value}" onclick="document.location='{if $link}{$link}{else}{$value}/{/if}' "/></span>
{if $br == 'after'}<br/>{/if}
