[searchposts]
	[fullresult]
		{include file="shortstory.tpl"}
	[/fullresult]
	[shortresult]
<article class="box story[fixed] fixed_story[/fixed]">
	<div class="box_in">
		<h2 class="title">[full-link]{title}[/full-link]</h2>
		[not-group=5]
		<ul class="story_icons">
			<li class="edit_btn">
				[edit]<i title="Edit">Edit</i>[/edit]
			</li>
		</ul>
		[/not-group]
		<div class="text">
			{short-story limit="200"}...
		</div>
	</div>
	<div class="meta">
		<ul class="right">
			<li class="category">
				<svg class="icon icon-cat"><use xlink:href="#icon-cat"></use></svg>
				<a href="{category-url}">{category}</a>
			</li>
		</ul>
		<ul class="left">
			<li><svg class="icon icon-info"><use xlink:href="#icon-info"></use></svg> {author}<span class="grey"> from <time datetime="{date=Y-m-d}">[day-news]{date}[/day-news]</time></span></li>
		</ul>
	</div>
</article>
	[/shortresult]
[/searchposts]
[searchcomments]
	[fullresult]
		{include file="comments.tpl"}
	[/fullresult]
	[shortresult]
	<div class="comment" id="{comment-id}">
		<div class="com_content">
			<h4 class="title" style="margin-top: 0;">{news_title}</h4>
			<div class="text">{comment limit="200"}</div>
		</div>
	</div>
	[/shortresult]
[/searchcomments]