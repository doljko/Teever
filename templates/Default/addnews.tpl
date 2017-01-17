<article class="box story">
	<div class="box_in">
		<h4 class="title h1">Add news article</h4>
		<div class="addform">
			<ul class="ui-form">
				<li class="form-group">
					<label for="title" class="imp">Title</label>
					<input type="text" name="title" id="title" value="{title}" class="wide" required>
				</li>
				[urltag]
				<li class="form-group">
					<label for="alt_name" class="imp">Article URL</label>
					<input type="text" name="alt_name" id="alt_name" value="{alt-name}" class="wide">
				</li>
				[/urltag]
				<li class="form-group">
					<label for="category" class="imp">Category</label>
					{category}
				</li>
				<li class="form-group">
					<label><a href="#" onclick="$('.addvote').toggle();return false;"><span class="plus_icon circle"><span>+</span></span> Add voting</a></label>
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="vote_title" >Voting title</label>
					<input type="text" name="vote_title" value="{votetitle}" class="wide" />
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="frage" >Question</label>
					<input type="text" name="frage" value="{frage}" class="wide" />
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="vote_body" >Answer choices</label>
					<textarea name="vote_body" rows="5" class="wide" placeholder="Each new line is one of the answer choices">{votebody}</textarea><br /><input type="checkbox" name="allow_m_vote" value="1" {allowmvote}> Allow multiple choice
				</li>
				<li class="form-group">
					<label for="short_story" class="imp">Introductory part</label>
					[not-wysywyg]
					<div class="bb-editor">
						{bbcode}
						<textarea name="short_story" id="short_story" onfocus="setFieldName(this.name)" rows="5" class="wide" required>{short-story}</textarea>
					</div>
					[/not-wysywyg]
					{shortarea}
				</li>
				<li class="form-group">
					<label for="full_story">Full part</label>
					[not-wysywyg]
					<div class="bb-editor">
						{bbcode}
						<textarea name="full_story" id="full_story" onfocus="setFieldName(this.name)" rows="12" class="wide" >{full-story}</textarea>
					</div>
					[/not-wysywyg]
					{fullarea}
				</li>
				<li class="form-group">
					<label for="alt_name">Keywords for a Tag Cloud</label>
					<input placeholder="Enter comma separated" type="text" name="tags" id="tags" value="{tags}" maxlength="150" autocomplete="off" class="wide">
				</li>
				<li class="form-group">
					<table style="width:100%">
						{xfields}
					</table>
				</li>
			[group=1,2]
				<li class="form-group">
					<div class="admin_checkboxs">{admintag}</div>
				</li>
			[/group]
			[recaptcha]
				<li class="form-group">{recaptcha}</li>
			[/recaptcha]
			[question]
				<li class="form-group">
					<label for="question_answer">{question}</label>
					<input placeholder="Enter Answer" type="text" name="question_answer" id="question_answer" class="wide" required>
				</li>
			[/question]
			</ul>
			<p style="margin: 20px 0 0 0;" class="grey"><span style="color: #e85319">*</span> - fields marked with asterisk are required.</p>
			<div class="form_submit">
				[sec_code]
					<div class="c-captcha">
						{sec_code}
						<input placeholder="Enter the code" title="Enter the code from the image" type="text" name="sec_code" id="sec_code" required>
					</div>
				[/sec_code]
				<button class="btn btn-big" type="submit" name="add"><b>Send</b></button>
				<button class="btn-border btn-big" onclick="preview()" type="submit" name="nview"><b>Preview</b></button>
			</div>
		</div>
	</div>
</article>