<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
        <title>{$title}</title>
        <link>{$link}</link>
        <description><![CDATA[{$decription}]]></description>
        <language>{$lang}</language>
		<image>
			<link>http://{$CI->site->url}</link>
			<url>http://{$CI->site->url}/templates/{$CI->site->template}/img/logo.png</url>
			<title>{$CI->site->name}</title>
		</image>
        {foreach $items as $item}
        <item>
          <title><![CDATA[{$item->title}]]></title>
		  <guid isPermaLink="true">{$item->link}</guid>
          <link>{$item->link}</link>
          <description><![CDATA[
			{$item->body}
      ]]></description>
		   {if $item->enclosure}<enclosure url="{$item->enclosure.url}" type="{$item->enclosure.type}"/>{/if}
           <author>{$item->author}</author>
           <pubDate>{? gmdate("D, d M Y H:i:s",strtotime($item->created_date)).' GMT'}</pubDate>
    </item>
    {/foreach}
    </channel></rss>