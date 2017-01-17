<article class="box story">
	{include file="modules/contacts.tpl"}
	{include file="modules/map.tpl"}
	<div class="box_in">
		<h4 class="title h1">Feedback</h4>
		<div class="addform">
			<ul class="ui-form">
			[not-logged]
			<li class="form-group combo">
				<div class="combo_field"><input placeholder="Your name" type="text" maxlength="35" name="name" id="name" class="wide" required></div>
				<div class="combo_field"><input placeholder="Your e-mail" type="email" maxlength="35" name="email" id="email" class="wide" required></div>
			</li>
			[/not-logged]
				<li class="form-group">
					<input placeholder="Topic" type="text" maxlength="45" name="subject" id="subject" class="wide" required>
				</li>
				<li class="form-group">
					<label>To</label>
					{recipient}
				</li>
				<li class="form-group">
					<textarea placeholder="Message" name="message" id="message" rows="8" class="wide" required></textarea>
				</li>
			[recaptcha]
				<li class="form-group">{recaptcha}</li>
			[/recaptcha]
			[question]
				<li class="form-group">
					<label for="question_answer">Question: {question}</label>
					<input placeholder="Answer" type="text" name="question_answer" id="question_answer" class="wide" required>
				</li>
			[/question]
			</ul>
			<div class="form_submit">
				[sec_code]
					<div class="c-captcha">
						{code}
						<input placeholder="Enter the code" title="Enter the code" type="text" name="sec_code" id="sec_code" required>
					</div>
				[/sec_code]
				<button class="btn btn-big" type="submit" name="send_btn"><b>Send</b></button>
			</div>
		</div>
	</div>
</article>