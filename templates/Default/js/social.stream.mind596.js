/*Name : TweeCool
 *version: 1.7 
 *Description: Get the latest tweets from twitter.
 *Website: www.tweecool.com
 *Licence: No licence, feel free to do whatever you want.
 *Author: TweeCool
 */
!function (t) { t.fn.extend({ tweecool: function (e) { function a(t) { var e = new Date, a = Date.parse(e), i = 1e3 * t, r = (a - i) / 1e3, o = 1, n = 60, s = 3600, _ = 86400, l = 604800, c = 2592e3, u = 31536e3; return r > o && n > r ? Math.round(r / o) + " seconds ago" : r >= n && s > r ? Math.round(r / n) + " minutes ago" : r >= s && _ > r ? Math.round(r / s) + " hours ago" : r >= _ && l > r ? Math.round(r / _) + " days ago" : r >= l && c > r ? Math.round(r / l) + " weeks ago" : r >= c && u > r ? Math.round(r / c) + " months ago" : "over a year ago" } var i = { username: "", limit: 5, profile_image: !0, show_time: !0, show_media: !1, show_media_size: "thumb", show_actions: !1, action_reply_icon: "&crarr;", action_retweet_icon: "&prop;", action_favorite_icon: "&#9733;", profile_img_url: "profile", show_retweeted_text: !1 }, e = t.extend(i, e); return this.each(function () { var i, r, o, n, s, _ = e, l = t(this), c = t("<ul class='slides'>").appendTo(l), u = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi, w = /@+(\w+)/gi, h = /#+(\w+)/gi; t.getJSON("https://www.api.tweecool.com/?screenname=" + _.username + "&count=" + _.limit, function (e) { return e.errors || null == e ? (l.html("No tweets available."), !1) : void t.each(e.tweets, function (t, l) { i = _.profile_image ? "tweet" == _.profile_img_url ? '<a href="https://twitter.com/' + _.username + "/status/" + l.id_str + '" target="_blank"><img src="' + e.user.profile_image_url + '" alt="' + _.username + '" /></a>' : '<a href="https://twitter.com/' + _.username + '" target="_blank"><img src="' + e.user.profile_image_url + '" alt="' + _.username + '" /></a>' : "", o = _.show_time ? a(l.timestamp) : "", r = _.show_media && l.media_url ? '<a href="https://twitter.com/' + _.username + "/status/" + l.id_str + '" target="_blank"><img class="tweets_media" src="' + l.media_url + ":" + _.show_media_size + '" alt="' + _.username + '" class="media" /></a>' : "", _.show_actions ? (n = '<div class="action-box"><ul>', n += '<li class="ab_reply"><a title="Reply" href="https://twitter.com/intent/tweet?in_reply_to=' + l.id_str + '">' + _.action_reply_icon + "</a></li>", n += '<li class="ab_retweet"><a title="Retweet" href="https://twitter.com/intent/retweet?tweet_id=' + l.id_str + '">' + _.action_retweet_icon + "</a>" + ("" != l.retweet_count_f ? "<span>" + l.retweet_count_f + "</span>" : "") + "</li>", n += '<li class="ab_favorite"><a title="Favorite" href="https://twitter.com/intent/favorite?tweet_id=' + l.id_str + '">' + _.action_favorite_icon + "</a>" + ("" != l.favorite_count_f ? "<span>" + l.favorite_count_f + "</span>" : "") + "</li>", n += "</ul></div>") : n = "", s = _.show_retweeted_text && l.retweeted_text ? l.retweeted_text : l.text, c.append("<li>" + i + '<div class="tweets_txt">' + s.replace(u, '<a href="$1" target="_blank">$1</a>').replace(w, '<a href="https://twitter.com/$1" target="_blank">@$1</a>').replace(h, '<a href="https://twitter.com/search?q=%23$1" target="_blank">#$1</a>') + r + " <span class='tweets_time'>" + o + "</span>" + n + "</div></li>") }) }).fail(function () { l.html("No tweets available") }) }) } }) }(jQuery);

/*
 * Facebook Wall
 * https://github.com/thomasclausen/jquery-facebook-wall
 * Under MIT License
 */
(function (n) { n.fn.facebook_wall = function (t) { function i(n) { return e(o(s(n))) } function e(n) { return n.replace(/(\r\n)|(\n\r)|\r|\n/g, "<br />") } function o(n) { return n.replace(/((http|https|ftp):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g, '<a href="$1" target="_blank">$1<\/a>') } function s(n) { return n.replace(/</g, "&lt;").replace(/>/g, "&gt;") } function f(n) { var r = new Date(n * 1e3), u = r.toGMTString(), i = Math.round((new Date).getTime() / 1e3) - n; return i < 10 ? " seconds ago" : i < 60 ? Math.round(i) + " seconds ago" : Math.round(i / 60) === 1 ? Math.round(i / 60) + " minuts ago" : Math.round(i / 60) < 60 ? Math.round(i / 60) + " minuts ago" : Math.round(i / 3600) === 1 ? Math.round(i / 3600) + " hour ago" : Math.round(i / 3600) < 24 ? Math.round(i / 3600) + " hours ago" : Math.round(i / 86400) === 1 ? Math.round(i / 86400) + " day ago" : Math.round(i / 86400) <= 10 ? Math.round(i / 86400) + " days ago" : t.text_labels.days[r.getDay()] + " " + r.getDate() + ". " + t.text_labels.months[r.getMonth()] + " " + r.getFullYear() } if (t.id !== undefined && t.access_token !== undefined) { t = n.extend({ id: "", access_token: "", limit: 15, timeout: 400, speed: 400, effect: "slide", locale: "en_US", avatar_size: "square", message_length: 200, show_guest_entries: !0, text_labels: { shares: { singular: "Shared % time", plural: "Shared % times" }, likes: { singular: "% Like", plural: "% Likes" }, comments: { singular: "% comment", plural: "% comments" }, like: "Like", comment: "Write comment", share: "Share", days: ["Sunday", "Monday", "Thuesday", "Wednesday", "Thursday", "Friday", "Saturday"], months: ["januari", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"] }, on_complete: null }, t); var u = "https://graph.facebook.com/", h = t.show_guest_entries === !1 ? "posts" : "feed", c = u + t.id + "/" + h + "/?access_token=" + t.access_token + "&limit=" + t.limit + "&locale=" + t.locale + "&date_format=U&callback=?", r = n(this); r.append('<div class="facebook-loading"><\/div>'); n.getJSON(c, function (e) { n.each(e.data, function () { var n = "", h = "", c = "", s = "", o, e; if (this.is_hidden === !1) { if (this.type === "link" ? h = "type-link " : this.type === "photo" ? h = "type-photo " : this.type === "status" ? h = "type-status " : this.type === "video" && (h = "type-video "), n += '<li class="post ' + h + "avatar-size-" + t.avatar_size + '">', n += '<div class="meta-header">', n += '<div class="avatar"><a href="http://www.facebook.com/profile.php?id=' + this.from.id + '" target="_blank" title="' + this.from.name + '"><img src="' + (u + this.from.id + "/picture?type=" + t.avatar_size) + '" alt="' + this.from.name + '" /><\/a><\/div>', n += '<div class="author"><a href="http://www.facebook.com/profile.php?id=' + this.from.id + '" target="_blank" title="' + this.from.name + '">' + this.from.name + "<\/a><\/div>", n += '<div class="date">' + f(this.created_time) + "<\/div>", n += "<\/div>", this.message !== undefined ? n += t.message_length > 0 && this.message.length > t.message_length ? '<div class="message">' + i(this.message.substring(0, t.message_length)) + "...<\/div>" : '<div class="message">' + i(this.message) + "<\/div>" : this.story !== undefined && (n += t.message_length > 0 && this.story.length > t.message_length ? '<div class="story">' + i(this.story.substring(0, t.message_length)) + "...<\/div>" : '<div class="story">' + i(this.story) + "<\/div>"), (this.type === "link" || this.type === "photo" || this.type === "video") && (c = this.picture !== undefined || this.object_id !== undefined ? " border-left" : "", n += '<div class="media' + c + ' clearfix">', this.picture !== undefined ? n += '<div class="image"><a href="' + this.link + '" target="_blank"><img src="' + this.picture + '" /><\/a><\/div>' : this.object_id !== undefined && (n += '<div class="image"><a href="' + this.link + '" target="_blank"><img src="' + (u + this.object_id + "/picture?type=album") + '" /><\/a><\/div>'), n += '<div class="media-meta">', this.name !== undefined && (n += '<div class="name"><a href="' + this.link + '" target="_blank">' + this.name + "<\/a><\/div>"), this.caption !== undefined && (n += '<div class="caption">' + i(this.caption) + "<\/div>"), this.description !== undefined && (n += '<div class="description">' + i(this.description) + "<\/div>"), n += "<\/div>", n += "<\/div>"), n += '<div class="meta-footer">', n += '<time class="date" datetime="' + this.created_time + '" pubdate>' + f(this.created_time) + "<\/time>", this.likes !== undefined && this.likes.data !== undefined && (n += this.likes.count !== undefined ? this.likes.count === 1 ? '<span class="seperator">&middot;<\/span><span class="likes">' + t.text_labels.likes.singular.replace("%", this.likes.count) + "<\/span>" : '<span class="seperator">&middot;<\/span><span class="likes">' + t.text_labels.likes.plural.replace("%", this.likes.count) + "<\/span>" : this.likes.data.length === 1 ? '<span class="seperator">&middot;<\/span><span class="likes">' + t.text_labels.likes.singular.replace("%", this.likes.data.length) + "<\/span>" : '<span class="seperator">&middot;<\/span><span class="likes">' + t.text_labels.likes.plural.replace("%", this.likes.data.length) + "<\/span>"), this.comments !== undefined && this.comments.data !== undefined && (n += this.comments.data.length === 1 ? '<span class="seperator">&middot;<\/span><span class="comments">' + t.text_labels.comments.singular.replace("%", this.comments.data.length) + "<\/span>" : '<span class="seperator">&middot;<\/span><span class="comments">' + t.text_labels.comments.plural.replace("%", this.comments.data.length) + "<\/span>"), n += this.shares !== undefined ? this.shares.count === 1 ? '<span class="seperator">&middot;<\/span><span class="shares">' + t.text_labels.shares.singular.replace("%", this.shares.count) + "<\/span>" : '<span class="seperator">&middot;<\/span><span class="shares">' + t.text_labels.shares.plural.replace("%", this.shares.count) + "<\/span>" : '<span class="seperator">&middot;<\/span><span class="shares">' + t.text_labels.shares.plural.replace("%", "0") + "<\/span>", s = this.id.split("_"), n += '<div class="actionlinks"><span class="like"><a href="http://www.facebook.com/permalink.php?story_fbid=' + s[1] + "&id=" + s[0] + '" target="_blank">' + t.text_labels.like + '<\/a><\/span><span class="seperator">&middot;<\/span><span class="comment"><a href="http://www.facebook.com/permalink.php?story_fbid=' + s[1] + "&id=" + s[0] + '" target="_blank">' + t.text_labels.comment + '<\/a><\/span><span class="seperator">&middot;<\/span><span class="share"><a href="http://www.facebook.com/permalink.php?story_fbid=' + s[1] + "&id=" + s[0] + '" target="_blank">' + t.text_labels.share + "<\/a><\/span><\/div>", n += "<\/div>", this.likes !== undefined && this.likes.data !== undefined) { } if (this.comments !== undefined && this.comments.data !== undefined) { for (n += '<ul class="comment-list">', e = 0; e < this.comments.data.length; e++) n += '<li class="comment">', n += '<div class="meta-header">', n += '<div class="avatar"><a href="http://www.facebook.com/profile.php?id=' + this.comments.data[e].from.id + '" target="_blank" title="' + this.comments.data[e].from.name + '"><img src="' + (u + this.comments.data[e].from.id + "/picture?type=" + t.avatar_size) + '" alt="' + this.comments.data[e].from.name + '" /><\/a><\/div>', n += '<div class="author"><a href="http://www.facebook.com/profile.php?id=' + this.comments.data[e].from.id + '" target="_blank" title="' + this.comments.data[e].from.name + '">' + this.comments.data[e].from.name + "<\/a><\/div>", n += '<time class="date" datetime="' + this.comments.data[e].created_time + '" pubdate>' + f(this.comments.data[e].created_time) + "<\/time>", n += "<\/div>", n += '<div class="message">' + i(this.comments.data[e].message) + "<\/div>", n += '<time class="date" datetime="' + this.comments.data[e].created_time + '" pubdate>' + f(this.comments.data[e].created_time) + "<\/time>", n += "<\/li>"; n += "<\/ul>" } n += "<\/li>"; r.append(n) } }) }).complete(function () { n(".facebook-loading", r).fadeOut(800, function () { n(this).remove(); for (var i = 0; i < r.children("li").length; i++) t.effect === "none" ? r.children("li").eq(i).show() : t.effect === "fade" ? r.children("li").eq(i).delay(i * t.timeout).fadeIn(t.speed) : r.children("li").eq(i).delay(i * t.timeout).slideDown(t.speed, function () { n(this).css("overflow", "visible") }) }); n.isFunction(t.on_complete) && t.on_complete.call(); }) } } })(jQuery);

/*
* Pixor - Copyright (c) 2015 Federico Schiocchet - Pixor (www.pixor.it)
*/
var facebook_token = "821948481218796|87fGsmQYzi0SBmJBp2bUv4dXTjM";
(function ($) {
    $(document).ready(function () {
        $("*[data-social-id].social-feed-fb").each(function () {
            var t = this;
            var count = 4;
            var optionsString = $(t).attr("data-options");
            var id = $(t).attr("data-social-id");
            var token = $(t).attr("data-token");
            if (isEmpty(token)) token = facebook_token;

            var optionsArr;
            var options = {
                access_token: token,
                limit: count,
                locale: "en_US",
                show_guest_entries: false
            }
            if (!isEmpty(optionsString)) {
                optionsArr = optionsString.split(",");
                options = getOptionsString(optionsString, options);
            }
            if (!isEmpty(id)) options["id"] = id;
            $(t).facebook_wall(options);
            if ($(t).hasClass("flexslider")) {
                var timerVar;
                timerVar = self.setInterval(function () {
                    if ($(t).find('li.post').length == options["limit"] && $(t).find('.facebook-loading').length == 0) {
                        $(t).html("<ul class='slides'>" + $(t).html() + "</ul>");

                        $(t).find("li").each(function (index) {
                            $(this).html("<div class='fb-container'>" + $(this).html() + '</div>');
                        });

                        $(t).initFlexSlider();
                        $(t).css("opacity", 1);
                        clearInterval(timerVar);
                    }
                }, 1000);
            }  
        });

        $("*[data-social-id].social-feed-tw").each(function () {
            var t = this;
            var optionsString = $(t).attr("data-options");
            var id = $(t).attr("data-social-id");
            var optionsArr;
            var options = {
                limit: 4,
                show_media: true
            }
            if (!isEmpty(optionsString)) {
                optionsArr = optionsString.split(",");
                options = getOptionsString(optionsString, options);
            }
            if (!isEmpty(id)) options["username"] = id;
            $(t).tweecool(options);
            if ($(t).hasClass("flexslider")) {
                var timerVar;
                timerVar = self.setInterval(function () {
                    if ($(t).find('ul li').length) {
                        $(t).initFlexSlider();
                        clearInterval(timerVar);
                    }
                }, 300);
            }
        });

    });
}(jQuery));

