<div class="page_form__inner">
	<h1 class="title h1">
		[registration]New user registration[/registration]
		[validation]User profile update[/validation]
	</h1>
	<div class="page_form__form">
		<div class="regtext">
		[registration]
			<b>Hello, dear visitor of our website!</b><br /><br />
			Registration on our site will allow you to become its full participant. 
			You will be able to add news on the website, write your comments, view the hidden text and more. In case of problems encountered during the registration process, please contact the website <a href="/index.php?do=feedback">Administrator</a>.
		[/registration]
		[validation]
			<b>Dear visitor,</b><br />
			Your account has been registered on our website, 
			but your profile information is incomplete. Please, fill in the additional fields of your profile.
		[/validation]
		</div>
		<ul class="ui-form">
		[registration]
			<li class="form-group">
				<label for="name">Login</label>
				<div class="login_check">
					<input type="text" name="name" id="name" class="wide" required>
					<button class="btn" title="Check username" onclick="CheckLogin(); return false;">Check username</button>
				</div>
				<div id="result-registration"></div>
			</li>
			<li class="form-group">
				<label for="password1">Password</label>
				<input type="password" name="password1" id="password1" class="wide" required>
			</li>
			<li class="form-group">
				<label for="password2">Confirm password</label>
				<input type="password" name="password2" id="password2" class="wide" required>
			</li>
			<li class="form-group">
				<label for="email">E-mail</label>
				<input type="email" name="email" id="email" class="wide" required>
			</li>
		[question]
			<li class="form-group">
				<label for="question_answer">{question}</label>
				<input placeholder="Answer" type="text" name="question_answer" id="question_answer" class="wide" required>
			</li>
		[/question]
		[sec_code]
			<li class="form-group">
				<div class="c-captcha">
					{reg_code}
					<input placeholder="Enter the code" title="Enter the code" type="text" name="sec_code" id="sec_code" required>
				</div>
			</li>
		[/sec_code]
		[recaptcha]
			<li>{recaptcha}</li>
		[/recaptcha]
		[/registration]
		[validation]
			<li class="form-group">
				<label for="fullname">Your name</label>
				<input type="text" id="fullname" name="fullname" class="wide">
			</li>
			<li class="form-group">
				<label for="land">Place of residence</label>
				<input type="text" id="land" name="land" class="wide">
			</li>
			<li class="form-group">
				<label for="image">About me</label>
				<textarea id="info" name="info" rows="5" class="wide"></textarea>
			</li>
			<li class="form-group">
				<label for="image">Avatar</label>
				<input type="file" id="image" name="image" class="wide">
			</li>
			<li class="form-group">
				<table class="xfields">
					{xfields}
				</table>
			</li>
		[/validation]
		</ul>
		<div class="form_submit">
			<button class="btn" name="submit" type="submit">Submit</button>
		</div>
	</div>
</div>



<div class="page addform">
	<table class="tableform">


	</table>
</div>