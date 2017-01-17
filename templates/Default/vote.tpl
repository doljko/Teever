<div id="votes" class="block_grey">
	<h4 class="title">{title}</h4>
	<div class="vote_more"><a href="#" onclick="ShowAllVotes(); return false;">Other polls...</a></div>
	[votelist]
	<form method="post" name="vote">
	[/votelist]
		<div class="vote_list">
			{list}
		</div>
	[voteresult]
		<div class="vote_votes grey">Total votes: {votes}</div>
	[/voteresult]
	[votelist]
		<input type="hidden" name="vote_action" value="vote">
		<input type="hidden" name="vote_id" id="vote_id" value="{vote_id}">
		<button title="Vote" class="btn btn-white" type="submit" onclick="doVote('vote'); return false;" ><b>Vote</b></button>
		<button title="Results" class="btn-border" type="button" onclick="doVote('results'); return false;" >
			<svg class="icon icon-votes"><use xlink:href="#icon-votes"></use></svg>
			<span class="title_hide">Results</span>
		</button>
	</form>
	[/votelist]
</div>