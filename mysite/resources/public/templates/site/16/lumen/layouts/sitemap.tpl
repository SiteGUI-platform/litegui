<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>{$site.name} {$html.type}</title>
    <link>https://{$site.url}</link>
    <description></description>
{foreach $api.pages AS $page}
    <item>
      <title>{$page.title}</title>
      <link>{$page.slug}</link>
      <guid>{$page.id}</guid>
{if $page.image}
      <enclosure url="{$page.image}" length="1000" type="image/jpg"></enclosure>
{/if}
      <pubDate>{$page.updated}</pubDate>
      <description>{$page.description}</description>
    </item>
{/foreach}
  </channel>
</rss>