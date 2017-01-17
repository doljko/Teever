[shortrss]<item>
<title>{title}</title>
<guid isPermaLink="true">{rsslink}</guid>
<link>{rsslink}</link>
<description>{short-story}</description>
<category>{category}</category>
<dc:creator>{rssauthor}</dc:creator>
<pubDate>{rssdate}</pubDate>
</item>[/shortrss]
[fullrss]<item>
<title>{title}</title>
<guid isPermaLink="true">{rsslink}</guid>
<link>{rsslink}</link>
<description><![CDATA[{short-story}]]></description>
<category><![CDATA[{category}]]></category>
<dc:creator>{rssauthor}</dc:creator>
<pubDate>{rssdate}</pubDate>
</item>[/fullrss]
[yandexrss]<item>
<title>{title}</title>
<link>{rsslink}</link>
<description>{short-story}</description>
<category>{category}</category>{images}
<pubDate>{rssdate}</pubDate>
<yandex:full-text>{full-story}</yandex:full-text>
</item>[/yandexrss]