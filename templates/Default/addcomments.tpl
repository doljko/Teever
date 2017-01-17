<div id="addcomment" class="addcomment">
	<div class="plus_icon"><span>Add comment</span></div>
	<div class="box_in">
		<h3>Add comment</h3>
		<ul class="ui-form">
		[not-logged]
			<li class="form-group combo">
				<div class="combo_field"><input placeholder="Name" type="text" name="name" id="name" class="wide" required></div>
				<div class="combo_field"><input placeholder="E-Mail" type="email" name="mail" id="mail" class="wide"></div>
			</li>
		[/not-logged]
			<li id="comment-editor">{editor}</li>    
		[recaptcha]
			<li>{recaptcha}</li>
		[/recaptcha]
		[question]
			<li class="form-group">
				<label for="question_answer">{question}</label>
				<input placeholder="Answer" type="text" name="question_answer" id="question_answer" class="wide" required>
			</li>
		[/question]
		</ul>
		<div class="form_submit">
		[sec_code]
			<div class="c-captcha">
				{sec_code}
				<input placeholder="Enter the code" title="Enter the code" type="text" name="sec_code" id="sec_code" required>
			</div>
		[/sec_code]
			<button class="btn btn-big" type="submit" name="submit" title="Add"><b>Add</b></button>
		</div>
	</div>
</div>