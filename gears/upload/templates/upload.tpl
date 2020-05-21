<div id="image-uploader">
	<ul>
		{foreach $items as $item}
		<li><a href="{$item.link}">{$item.name}</a></li>
		{/foreach}
	</ul>
</div>
<script type="text/javascript" src="/gears/upload/js/inline/upload.tabs.js"></script>
