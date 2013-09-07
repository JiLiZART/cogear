{foreach $elements as $elem}
{switch $elem.type}
{case 'title'}
<h1>{$elem.text}</h1>
{break}
{case 'line'}
<div class="line">
 <div class="left">
  {$elem.left}:
 </div>
 <div class="right">
  {$elem.right}
 </div>
</div>
{break}
{/switch}
{/foreach}