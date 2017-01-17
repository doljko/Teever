/*!
 * jQuery pagination plugin v1.2.5
 * http://esimakin.github.io/twbs-pagination/
 *
 * Copyright 2014, Eugene Simakin
 * Released under Apache 2.0 license
 * http://apache.org/licenses/LICENSE-2.0.html
 */
; (function (e, d, a, f) { var b = e.fn.twbsPagination; var c = function (j, h) { this.$element = e(j); this.options = e.extend({}, e.fn.twbsPagination.defaults, h); if (this.options.startPage < 1 || this.options.startPage > this.options.totalPages) { throw new Error("Start page option is incorrect") } this.options.totalPages = parseInt(this.options.totalPages); if (isNaN(this.options.totalPages)) { throw new Error("Total pages option is not correct!") } this.options.visiblePages = parseInt(this.options.visiblePages); if (isNaN(this.options.visiblePages)) { throw new Error("Visible pages option is not correct!") } if (this.options.totalPages < this.options.visiblePages) { this.options.visiblePages = this.options.totalPages } if (this.options.onPageClick instanceof Function) { this.$element.first().bind("page", this.options.onPageClick) } if (this.options.href) { var g, k = this.options.href.replace(/[-\/\\^$*+?.|[\]]/g, "\\$&"); k = k.replace(this.options.hrefVariable, "(\\d+)"); if ((g = new RegExp(k, "i").exec(d.location.href)) != null) { this.options.startPage = parseInt(g[1], 10) } } var i = (typeof this.$element.prop === "function") ? this.$element.prop("tagName") : this.$element.attr("tagName"); if (i === "UL") { this.$listContainer = this.$element } else { this.$listContainer = e("<ul></ul>") } this.$listContainer.addClass(this.options.paginationClass); if (i !== "UL") { this.$element.append(this.$listContainer) } this.render(this.getPages(this.options.startPage)); this.setupEvents(); return this }; c.prototype = { constructor: c, destroy: function () { this.$element.empty(); this.$element.removeData("twbs-pagination"); this.$element.unbind("page"); return this }, show: function (g) { if (g < 1 || g > this.options.totalPages) { throw new Error("Page is incorrect.") } this.render(this.getPages(g)); this.setupEvents(); this.$element.trigger("page", g); return this }, buildListItems: function (g) { var j = e(); if (this.options.first) { j = j.add(this.buildItem("first", 1)) } if (this.options.prev) { var l = g.currentPage > 1 ? g.currentPage - 1 : this.options.loop ? this.options.totalPages : 1; j = j.add(this.buildItem("prev", l)) } for (var h = 0; h < g.numeric.length; h++) { j = j.add(this.buildItem("page", g.numeric[h])) } if (this.options.next) { var k = g.currentPage < this.options.totalPages ? g.currentPage + 1 : this.options.loop ? 1 : this.options.totalPages; j = j.add(this.buildItem("next", k)) } if (this.options.last) { j = j.add(this.buildItem("last", this.options.totalPages)) } return j }, buildItem: function (i, j) { var h = e("<li></li>"), k = e("<a></a>"), g = null; switch (i) { case "page": g = j; h.addClass(this.options.pageClass); break; case "first": g = this.options.first; h.addClass(this.options.firstClass); break; case "prev": g = this.options.prev; h.addClass(this.options.prevClass); break; case "next": g = this.options.next; h.addClass(this.options.nextClass); break; case "last": g = this.options.last; h.addClass(this.options.lastClass); break; default: break } h.data("page", j); h.data("page-type", i); h.append(k.attr("href", this.makeHref(j)).html(g)); return h }, getPages: function (j) { var g = []; var k = Math.floor(this.options.visiblePages / 2); var l = j - k + 1 - this.options.visiblePages % 2; var h = j + k; if (l <= 0) { l = 1; h = this.options.visiblePages } if (h > this.options.totalPages) { l = this.options.totalPages - this.options.visiblePages + 1; h = this.options.totalPages } var i = l; while (i <= h) { g.push(i); i++ } return { currentPage: j, numeric: g } }, render: function (g) { this.$listContainer.children().remove(); this.$listContainer.append(this.buildListItems(g)); var h = this.$listContainer.children(); h.filter(function () { return e(this).data("page") === g.currentPage && e(this).data("page-type") === "page" }).addClass(this.options.activeClass); h.filter(function () { return e(this).data("page-type") === "first" }).toggleClass(this.options.disabledClass, g.currentPage === 1); h.filter(function () { return e(this).data("page-type") === "last" }).toggleClass(this.options.disabledClass, g.currentPage === this.options.totalPages); h.filter(function () { return e(this).data("page-type") === "prev" }).toggleClass(this.options.disabledClass, !this.options.loop && g.currentPage === 1); h.filter(function () { return e(this).data("page-type") === "next" }).toggleClass(this.options.disabledClass, !this.options.loop && g.currentPage === this.options.totalPages) }, setupEvents: function () { var g = this; this.$listContainer.find("li").each(function () { var h = e(this); h.off(); if (h.hasClass(g.options.disabledClass) || h.hasClass(g.options.activeClass)) { h.click(function (i) { i.preventDefault() }); return } h.click(function (i) { !g.options.href && i.preventDefault(); g.show(parseInt(h.data("page"), 10)) }) }) }, makeHref: function (g) { return this.options.href ? this.options.href.replace(this.options.hrefVariable, g) : "#" } }; e.fn.twbsPagination = function (i) { var h = Array.prototype.slice.call(arguments, 1); var k; var l = e(this); var j = l.data("twbs-pagination"); var g = typeof i === "object" && i; if (!j) { l.data("twbs-pagination", (j = new c(this, g))) } if (typeof i === "string") { k = j[i].apply(j, h) } return (k === f) ? l : k }; e.fn.twbsPagination.defaults = { totalPages: 0, startPage: 1, visiblePages: 5, href: false, hrefVariable: "{{number}}", first: "First", prev: "Previous", next: "Next", last: "Last", loop: false, onPageClick: null, paginationClass: "pagination", nextClass: "next", prevClass: "prev", lastClass: "last", firstClass: "first", pageClass: "page", activeClass: "active", disabledClass: "disabled" }; e.fn.twbsPagination.Constructor = c; e.fn.twbsPagination.noConflict = function () { e.fn.twbsPagination = b; return this } })(jQuery, window, document);

/*
* Pixor - Copyright (c) 2015 Federico Schiocchet - Pixor (www.pixor.it)
*/

(function ($) {
    var isRLI;
    $.fn.initTwbsPagination = function () {
        var opt = $(this).attr("data-options");
        var a = $(this).attr("data-pagination-anima");
        var p = parseInt($(this).attr("data-page-items"), 10);
        var c = $(this).closest(".grid-list");
        var t = $(c).find(".grid-box .grid-item");
        var n = $(t).length;
        var type = "pagination";
        if ($(this).hasClass('load-more-grid')) type = 'load-more';

        $(t).css("display", "none");
        for (var i = 0; i < p; i++) {
            $($(t)[i]).css("display", "block");
        }

        if (type == 'pagination') {
            var optionsArr;
            var options = {
                totalPages: Math.ceil(n / p),
                visiblePages: 7,
                first: "<i class='fa fa-angle-double-left'></i> First",
                last: "Last <i class='fa fa-angle-double-right'></i>",
                next: "Next <i class='fa fa-angle-right'></i>",
                prev: " <i class='fa fa-angle-left'></i> Previous",
                onPageClick: function (event, page) {
                    $(t).css("display", "none");
                    for (var i = (p * (page - 1)) ; i < (p * (page)) ; i++) {
                        var o = $($(t)[i]);
                        if (!isEmpty(a)) {
                            $(o).css("opacity", "0");
                            $(o).showAnima(a);
                        }
                        $(o).css("display", "block");
                        if (isRLI) $(o).renderLoadedImgs();
                    }
                    if (!isEmpty(opt) && opt.indexOf("scrollTop:true") != -1) $(c).scrollTo();
                }
            }
            if (!isEmpty(opt)) {
                optionsArr = opt.split(",");
                options = getOptionsString(opt, options);
            }
            $(this).twbsPagination(options);
        }
        if (type == 'load-more') {
            if (!isEmpty(opt) && opt.indexOf("lazyLoad:true") != -1) {
                $(window).scroll(function () {
                    if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                        loadMoreGrid(this);
                    }
                });
            }
            $(this).click(function () {
                loadMoreGrid(this);
            });
        }
        function loadMoreGrid(obj) {
            var page = $(obj).attr("data-current-page");
            if (isEmpty(page)) page = 1;
            page++;
            $(obj).attr("data-current-page", page);
            var s = p * (page - 1);
            var e = p * (page);

            for (var i = s ; i < (p * (page)) ; i++) {
                var o = $($(t)[i]);
                if (!isEmpty(a)) {
                    $(o).css("opacity", "0");
                    $(o).showAnima(a);
                }
                $(o).css("display", "block");
            }
            if (e >= n) $(obj).hide(300);
        }
    }
    $(document).ready(function () {
        isRLI = $.fn.renderLoadedImgs;
        $(".pagination-grid,.load-more-grid").each(function () {
            $(this).initTwbsPagination();
        });
       
        //ALBUM
        var alb = ".cont-album-box";
        $(".album-item").hide();

        $("body").on("click", ".album-box", function () {
            var t = $(this).closest(".album-main");
            var a = $(t).attr("data-album-anima");
            var ida = $("#" + $(this).attr("data-album-id"));
            if (isEmpty(ida)) {
                ida = $(t).find(alb + " .album-item:eq(" + $(this).index() + ")");
            }
            $(alb + " .album-item").hide();
            $(t).find(".album-list").hide();
            $(t).find(alb + " .album-title span").html($(this).find(".album-name").html().replace("<br>", " ").replace("<br />", " "));
            $(t).find(alb + " .album-title").show();
            if (!isEmpty(a)) {
                $(ida).css("opacity", 0);
                $(ida).showAnima(a);
            }
            $(ida).css("display", "block");
            if ($.isFunction($.fn.initIsotope)) $(ida).find(".maso-list").initIsotope();
            if (isRLI) $(t).find(ida).renderLoadedImgs();

            $(ida).find(".load-more-maso").attr("data-current-page", "1").css("display", "inline-block");
        });
        $("body").on("click", alb + " .album-title", function () {
            var t = $(this).closest(".album-main");
            var a = $(t).attr("data-album-anima");
            var b = $(t).find(".album-list");
            $(alb + " .album-item").hide();
            $(alb + " .album-title").hide();
            if (!isEmpty(a)) {
                $(b).css("opacity", 0);
                $(b).showAnima(a);
            }
            $(b).css("display", "block");
        });
    });

}(jQuery));


