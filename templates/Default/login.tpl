[not-group=5]
<ul id="login_pane">
	<li class="dropdown">
		<a data-toggle="dropdown" class="btn-border" href="{profile-link}">
			<b class="over">{login}</b><span title="New messages - {new-pm}" class="pm_num">{new-pm}</span>
		</a>
		<div class="dropdown-form dot logged">
			<div class="login_pane__info">
				<div class="avatar">
					<a href="{profile-link}"><span class="cover" style="background-image: url({foto});">{login}</span></a>
				</div>
				[admin-link]<div class="login_pane__admin"><a href="{admin-link}" target="_blank">Administration Panel</a></div>[/admin-link]
			</div>
			<ul class="login_pane__menu">
				<li><a href="{pm-link}">Messages <span class="right grey"><b>{new-pm}</b> of {all-pm}</span></a></li>
				<li><a href="{favorites-link}">My favorites <span class="right grey"><b>{favorite-count}</b></span></a></li>
				<li><a href="{newposts-link}">Unread News</a></li>
			</ul>
			<div class="login_pane__foot">
				<a class="right" href="{logout-link}">Logout</a>
				<a href="{addnews-link}"><span class="plus_icon circle"><span>+</span></span>Add news article</a>
			</div>
		</div>
	</li>
</ul>
[/not-group]
[group=5]
<ul id="login_pane">
	<li class="dropdown">
		<a data-toggle="dropdown" class="btn-border" href="#"><b>Login</b></a>
		<form class="dropdown-form dot" method="post">
			<div class="soc_links">
				[vk]<a href="{vk_url}" target="_blank" class="soc_vk">
					<svg class="icon icon-vk"><use xlink:href="#icon-vk"/></svg>
				</a>[/vk]
				[yandex]<a href="{yandex_url}" target="_blank" class="soc_ya">
					<svg class="icon icon-ya"><use xlink:href="#icon-ya"/></svg>
				</a>[/yandex]
				[facebook]<a href="{facebook_url}" target="_blank" class="soc_fb">
					<svg class="icon icon-fb"><use xlink:href="#icon-fb"/></svg>
				</a>[/facebook]
				[google]<a href="{google_url}" target="_blank" class="soc_gp">
					<svg class="icon icon-gp"><use xlink:href="#icon-gp"/></svg>
				</a>[/google]
				[odnoklassniki]<a href="{odnoklassniki_url}" target="_blank" class="soc_od">
					<svg class="icon icon-od"><use xlink:href="#icon-od"/></svg>
				</a>[/odnoklassniki]
				[mailru]<a href="{mailru_url}" target="_blank" class="soc_mail">
					<svg class="icon icon-mail"><use xlink:href="#icon-mail"/></svg>
				</a>[/mailru]
			</div>
			<ul class="login_form">
				<li>
					<label for="login_name">{login-method}</label>
					<input placeholder="{login-method}" type="text" name="login_name" id="login_name">
					<svg class="icon icon-login"><use xlink:href="#icon-login"></use></svg>
				</li>
				<li class="login_input-btn">
					<label for="login_password">Password</label>
					<input placeholder="Password" type="password" name="login_password" id="login_password">
					<svg class="icon icon-pass"><use xlink:href="#icon-pass"></use></svg>
					<button class="btn" onclick="submit();" type="submit" title="Submit">
						<svg class="icon icon-right"><use xlink:href="#icon-right"></use></svg>
						<span class="title_hide">Submit</span>
					</button>
				</li>
			</ul>
			<input name="login" type="hidden" id="login" value="submit">
			<div class="login_form__foot">
				<a class="right" href="{registration-link}"><b>Registration</b></a>
				<a href="{lostpassword-link}">Forgot password?</a>
			</div>
		</form>
	</li>

</ul>
[/group]