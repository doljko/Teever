<div class="block_grey">
	<h4 class="title">{question}</h4>
		<div class="vote_list">
			{list}
		</div>
	[voted]
		<div class="vote_votes grey">Total votes: {votes}</div>
	[/voted]
	[not-voted]
		<button title="Vote" class="btn btn-white" type="submit" onclick="doPoll('vote', '{news-id}'); return false;" ><b>Vote</b></button>
		<button title="Results" class="btn-border" type="button" onclick="doPoll('results', '{news-id}'); return false;">
			<svg class="icon icon-votes"><use xlink:href="#icon-votes"></use></svg>
			<span class="title_hide">Results</span>
		</button>
	[/not-voted]
</div>