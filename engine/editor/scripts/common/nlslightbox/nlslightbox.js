function NlsLightBox(r) {
  function m(a) {
    if (n) {
      var c = parseInt(a.style.width, 10) + 1;
      a.style.width = c + "px";
      a.style.width = c - 1 + "px";
    }
  }
  function p(a) {
    var c = a.childNodes[0];
    return c && 1 == c.nodeType && "IMG" == c.tagName ? c : a;
  }
  var f = this, l = window.navigator.userAgent, q = 0 <= l.indexOf("MSIE"), t = 0 <= l.indexOf("MSIE 7.0"), n = 0 <= l.indexOf("MSIE 8.0"), g = !t && !n && q;
  IEBackCompat = q && "BackCompat" == document.compatMode;
  this.id = r;
  this.opts = {showOnLoaded:!1, title:"", type:"iframe", center:!0, centerOnResize:!0, overlay:!0, floatType:"fixed", adjX:0, adjY:0, scrAdjW:0, scrAdjH:0, zoom:!1};
  this.gropts = [];
  this.rtopts = {};
  this.rt = {w:"400px", h:"250px", x:"10px", y:"10px", iCnt:null};
  this.loadConfig = function(a) {
    this.rtopts = a;
    for (var c in this.opts) {
      "undefined" == typeof this.rtopts[c] && (this.rtopts[c] = this.opts[c]);
    }
    this.rt.adjX = parseInt(this.rtopts.adjX, 10);
    this.rt.adjY = parseInt(this.rtopts.adjY, 10);
    if (document.all) {
      try {
        document.execCommand("BackgroundImageCache", !1, !0);
      } catch (b) {
      }
    }
  };
  this.show = function(a) {
    this.loadConfig(a);
    this.paint();
    a = this.rtopts;
    this.setSize(a.width ? a.width : this.rt.w, a.height ? a.height : this.rt.h, a.center);
    a.center ? this.center() : this.setPosition(parseInt(a.left ? a.left : this.rt.x, 10) + this.rt.adjX, parseInt(a.top ? a.top : this.rt.y, 10) + this.rt.adjY);
  };
  this.paint = function() {
    var a = document.body, c = NlsLightBox.$("box_overlay");
    c || (c = NlsLightBox.$crtElement("div", {id:"box_overlay", className:"box_overlay"}, {display:"none", position:g ? "absolute" : "fixed"}), a.appendChild(c));
    c = "fixed";
    "fixed" == this.rtopts.floatType ? g && (c = "absolute", this.rtopts.floatType = "anim") : c = "absolute";
    if (NlsLightBox.$(f.id)) {
      this.rtopts.parent && this.rtopts.parent.appendChild(this.rt.lb);
    } else {
      a = NlsLightBox.$crtElement("div", {id:"dd$" + f.id}, {display:"none", position:g || IEBackCompat ? "absolute" : "fixed", "z-index":99999, "background-color":"#ffffff", filter:"alpha(opacity=25)", opacity:.25, "-moz-opacity":.25, border:"#999999 1px solid"});
      c = NlsLightBox.$crtElement("div", {id:f.id, className:"box_container"}, {display:"none", position:c});
      c.innerHTML = "<div id='@id$box_close' class='box_close' onclick='NlsLightBox.objs.@id.close()'></div>                <div id='@id$box_title' class='box_title' ><div id='@id$title_text' class='title_text'></div></div><div id='@id$content_area' class='content_area' style='position:relative'>                  <div id='@id$box_loading' class='box_loading' style='position:absolute;display:none;width:100%;height:100%'></div>                  <div id='@id$box_content' class='box_content'></div>                </div>                                <a href='#' id='@id$box_prev' class='box_prev' onclick='NlsLightBox.objs.@id.groupPrev();return false;'></a>                <a href='#' id='@id$box_next' class='box_next' onclick='NlsLightBox.objs.@id.groupNext();return false;'></a>               ".replace(/@id/ig, 
      this.id);
      var b = this.rtopts.parent;
      b || (b = document.body);
      b.appendChild(a);
      b.appendChild(c);
      this.rt.lb = c;
      a = NlsLightBox.$crtElement("div", {id:f.id + "$box_progress", className:"box_loading"}, {visibility:"hidden", position:"absolute"});
      document.body.appendChild(a);
      this.rt.pr = a;
      this.rt.lp = NlsLightBox.$(this.id + "$box_loading");
      this.rt.ca = NlsLightBox.$(this.id + "$content_area");
      this.rt.bc = NlsLightBox.$(this.id + "$box_content");
      this.rt.tl = NlsLightBox.$(this.id + "$box_title");
      this.rt.cl = NlsLightBox.$(this.id + "$box_close");
      this.rt.tc = NlsLightBox.$(this.id + "$title_text");
      this.rt.pv = NlsLightBox.$(this.id + "$box_prev");
      this.rt.nx = NlsLightBox.$(this.id + "$box_next");
      a = function() {
        f.rtopts.centerOnResize && f.center();
        f.updateOverlay();
      };
      window.attachEvent ? window.attachEvent("onresize", a) : window.addEventListener && window.addEventListener("resize", a, !0);
      if ("anim" == this.rtopts.floatType && NlsAnimation) {
        var d = new NlsAnimation, e = {duration:500, to:function() {
          var a = NlsLightBox.getScrollXY();
          return {left:f.rt.cx + a.x + "px", top:f.rt.cy + a.y + "px"};
        }, onComplete:function() {
          setTimeout(function() {
            d.move(f.rt.lb, e);
          }, 10);
        }}, a = function() {
          d.move(f.rt.lb, e);
        };
        window.attachEvent ? window.attachEvent("onscroll", a) : window.addEventListener && window.addEventListener("scroll", a, !0);
      }
      if (g) {
        var k = f.rt.lb.getElementsByTagName("div"), l = /\.png/gi
      }
      if (k) {
        for (b = 0;b < k.length;b++) {
          a = k[b].currentStyle.backgroundImage, a.match(l) && (a = a.split('"')[1], c = "no-repeat" == k[b].currentStyle.backgroundRepeat ? "crop" : "scale", k[b].style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + a + "',sizingMethod='" + c + "')", k[b].style.backgroundImage = "none");
        }
      }
    }
    0 == this.rtopts.titleBar && (this.rt.tl.style.display = "none");
    0 == this.rtopts.closeButton && (this.rt.cl.style.display = "none");
    null != this.rt.iCnt && (NlsLightBox.moveChilds(this.rt.bc, this.rt.iCnt), this.rt.iCnt = null);
    this.rt.bc.innerHTML = "";
    this.rt.bc.style.height = "";
    this.rt.pv.style.display = "none";
    this.rt.nx.style.display = "none";
    this.rt.tc.innerHTML = "" == this.rtopts.title ? this.rtopts.url : this.rtopts.title;
  };
  this.load = function() {
    switch(this.rtopts.type) {
      case "iframe":
        this.$openIframe(this.rtopts);
        break;
      case "ajax":
        this.$openAJAX(this.rtopts);
        break;
      case "image":
        this.$openImage(this.rtopts);
        break;
      case "inline":
        this.$openInline(this.rtopts);
    }
  };
  this.setPosition = function(a, c, b) {
    var d = this.rt, e = {x:0, y:0};
    "fixed" != this.rtopts.floatType && (e = NlsLightBox.getScrollXY());
    d.cx = a - (b ? e.x : 0);
    d.cy = c - (b ? e.y : 0);
    d.x = a + (b ? 0 : e.x);
    d.y = c + (b ? 0 : e.y);
    d.lb.style.left = d.x + "px";
    d.lb.style.top = d.y + "px";
  };
  this.setSize = function(a, c, b) {
    var d = this.rt;
    d.w = parseInt(a, 10);
    d.h = parseInt(c, 10);
    d.lb.style.width = d.w + "px";
    d.lb.style.height = d.h + "px";
    1 == b && this.center();
  };
  this.center = function() {
    var a = NlsLightBox.getCenterXY(this.rt.w, this.rt.h);
    this.setPosition(a.x + this.rt.adjX, a.y + this.rt.adjY);
  };
  this.setTitle = function(a) {
    this.rt.tc && (this.rt.tc.innerHTML = a);
    this.rtopts.title = a;
  };
  this.open = function(a) {
    this.show(a);
    a = this.rtopts;
    a.showOnLoaded ? this.load() : (a.zoom && "none" == this.rt.lb.style.display ? this.$zoom({srcObj:a.srcObj, to:{left:f.rt.x, top:f.rt.y, width:f.rt.w, height:f.rt.h}, onComplete:function() {
      f.load();
    }}) : this.load(), this.rt.lb.style.display = "block");
    this.showProgress();
    a.overlay && !a.zoom && this.showOverlay();
  };
  this.close = function() {
    "none" != this.rt.lb.style.display && (this.rtopts.onClose && 0 == this.rtopts.onClose(this) || (this.rtopts.zoom ? this.$zoom({srcObj:f.rtopts.srcObj, type:"out", to:NlsLightBox.$PD(f.rtopts.srcObj, "fixed" != this.rtopts.floatType), onComplete:function() {
      f.$close();
    }}) : this.$close()));
  };
  this.$close = function() {
    this.rt.lb.style.display = "none";
    NlsAnimation && NlsAnimation.setOpacity(f.rt.ca, 100);
    null != this.rt.iCnt ? (NlsLightBox.moveChilds(this.rt.bc, this.rt.iCnt), this.rt.iCnt = null) : this.rt.bc.innerHTML = "";
    this.rtopts.overlay && this.hideOverlay();
  };
  this.groupOpen = function(a, c, b) {
    for (var d = 0;d < a.length;d++) {
      if (c) {
        for (var e in c) {
          "undefined" == typeof a[d][e] && (a[d][e] = c[e]);
        }
      }
      a[d].group = !0;
    }
    this.rt.pnt = b ? b : 0;
    this.gropts = a;
    this.open(this.gropts[this.rt.pnt]);
  };
  this.groupNext = function() {
    this.groupHasNext() && this.rt.pnt++;
    this.open(this.gropts[this.rt.pnt]);
  };
  this.groupPrev = function() {
    this.groupHasPrev() && this.rt.pnt--;
    this.open(this.gropts[this.rt.pnt]);
  };
  this.groupHasPrev = function() {
    return this.rtopts.group && 0 < this.rt.pnt;
  };
  this.groupHasNext = function() {
    return this.rtopts.group && this.rt.pnt < this.gropts.length - 1;
  };
  this.showProgress = function() {
    if (this.rtopts.showOnLoaded) {
      var a = this.rt.pr, c = NlsLightBox.getCenterXY(a.offsetWidth, a.offsetHeight), b = {x:0, y:0};
      g ? b = NlsLightBox.getScrollXY() : a.style.position = "fixed";
      a.style.left = c.x + b.x + "px";
      a.style.top = c.y + b.y + "px";
      a.style.visibility = "visible";
    } else {
      this.rt.lp.style.display = "block";
    }
  };
  this.hideProgress = function() {
    this.rt.lp.style.display = "none";
    this.rt.pr.style.visibility = "hidden";
  };
  this.showOverlay = function() {
    this.updateOverlay(!0);
    NlsLightBox.$("box_overlay").style.display = "block";
  };
  this.updateOverlay = function(a) {
    var c = NlsLightBox.$("box_overlay");
    if (c && ("block" == c.style.display || a)) {
      a = NlsLightBox.getClientSize();
      if (g) {
        var b = document.body, d = document.documentElement, e;
        e = Math.min(Math.max(b.scrollWidth, d.scrollWidth), Math.max(b.offsetWidth, d.offsetWidth));
        b = (d.scrollHeight < d.offsetHeight || b.scrollHeight < b.offsetHeight ? Math.min : Math.max)(Math.max(b.scrollHeight, d.scrollHeight), Math.max(b.offsetHeight, d.offsetHeight));
        a.w = Math.max(a.w, e);
        a.h = Math.max(a.h, b);
      }
      c.style.width = a.w + "px";
      c.style.height = a.h + "px";
    }
  };
  this.hideOverlay = function() {
    NlsLightBox.$("box_overlay").style.display = "none";
  };
  this.onload = function(a) {
    return !0;
  };
  this.register = function(a, c) {
    var b = [], d;
    a instanceof Array ? b = a : b[0] = a;
    for (var e = 0;e < b.length;e++) {
      if (d = NlsLightBox.$(b[e])) {
        d.onclick = function() {
          c.url = this.href;
          !c.title && this.title && (c.title = this.title);
          c.srcObj || (c.srcObj = p(this));
          f.open(c);
          return !1;
        };
      }
    }
    return this;
  };
  this.registerGroup = function(a, c) {
    for (var b = [], d = 0;d < a.length;d++) {
      if (el = NlsLightBox.$(a[d])) {
        b[d] = {url:el.href, srcObj:p(el)}, el.title && (b[d].title = el.title), el.gpnt = d, el.onclick = function() {
          f.groupOpen(b, c, this.gpnt);
          return !1;
        };
      }
    }
    return this;
  };
  this.$openIframe = function(a) {
    if ("" != a.url) {
      var c = this.rt.bc;
      c.style.height = "100%";
      var b = document.createElement("iframe");
      b.style.width = "100%";
      b.style.height = "100%";
      b.frameBorder = 0;
      c.appendChild(b);
      b.onload = function() {
        f.$onloadCallback();
      };
      setTimeout(function() {
        b.attachEvent && b.attachEvent("onload", function() {
          f.$onloadCallback();
        });
        b.src = a.url;
      }, 50);
    }
  };
  this.$openAJAX = function(a) {
    if ("" != a.url) {
      var c = NlsLightBox.createRequest();
      c.open("get", a.url, !0);
      c.onreadystatechange = function() {
        4 != c.readyState || 200 != c.status && 304 != c.status || (f.rt.bc.innerHTML = c.responseText, setTimeout(function() {
          f.$onloadCallback();
        }, 50));
      };
      c.send(null);
    }
  };
  this.$openImage = function(a) {
    if ("" != a.url) {
      var c = document.createElement("img");
      this.rt.img = c;
      c.onload = function() {
        f.$onloadCallback();
      };
      setTimeout(function() {
        c.src = a.url;
      }, 50);
    }
  };
  this.$openInline = function(a) {
    "" != a.url && (a = a.url.split("#"), a = NlsLightBox.$(a[1]), this.rt.iCnt = a, NlsLightBox.moveChilds(a, f.rt.bc), setTimeout(function() {
      f.$onloadCallback();
    }, 50));
  };
  this.$onloadCallback = function() {
    this.hideProgress();
    this.groupHasPrev() && (this.rt.pv.href = this.gropts[this.rt.pnt - 1].url, this.rt.pv.style.display = "block");
    this.groupHasNext() && (this.rt.nx.href = this.gropts[this.rt.pnt + 1].url, this.rt.nx.style.display = "block");
    this.rtopts.zoom || 1 != this.rtopts.showOnLoaded || (this.rt.lb.style.display = "block");
    var a = this.rt.bc;
    switch(this.rtopts.type) {
      case "iframe":
        var c = "";
        try {
          c = a.childNodes[0].contentWindow.document.title;
        } catch (b) {
        }
        "" != c && this.setTitle(c);
        break;
      case "image":
        NlsLightBox.fitToClient(this.rt.img, {avW:this.rtopts.scrAdjW, avH:this.rtopts.scrAdjH}), "none" != this.rt.lb.style.display && NlsAnimation && (new NlsAnimation).resize(this.rt.lb, {delay:200, duration:700, to:{width:this.rt.img.width, height:this.rt.img.height}, onAnimate:function(a) {
          f.setSize(a.width, a.height + "px", f.rtopts.center);
          return !1;
        }, onComplete:function() {
          a.appendChild(f.rt.img);
          m(f.rt.lb);
        }, onAbort:function() {
          a.appendChild(f.rt.img);
          m(f.rt.lb);
        }}) || (this.setSize(this.rt.img.width, this.rt.img.height, this.rtopts.center), a.appendChild(f.rt.img)), this.rt.pv.style.height = this.rt.lb.style.height, this.rt.nx.style.height = this.rt.lb.style.height;
    }
    this.rtopts.zoom && 1 == this.rtopts.showOnLoaded && "none" == this.rt.lb.style.display && this.$zoom({srcObj:f.rtopts.srcObj, to:{left:this.rt.x, top:this.rt.y, width:this.rt.w, height:this.rt.h}});
    this.onload();
  };
  this.$zoom = function(a) {
    this.rt.lb.style.overflow = "hidden";
    this.rt.ca.style.overflow = "hidden";
    if ("out" != a.type) {
      var c = NlsLightBox.$PD(a.srcObj);
      this.setSize(c.width, c.height, !1);
      this.setPosition(c.left, c.top);
    }
    this.rt.lb.style.display = "block";
    (new NlsAnimation).zoom(this.rt.lb, {duration:500, to:a.to, type:a.type ? a.type : "in", onAnimate:function(a) {
      f.setSize(a.width, a.height, !1);
      f.setPosition(a.left, a.top, !0);
      NlsAnimation.setOpacity(f.rt.ca, a.$opa);
    }, onComplete:function() {
      f.rt.lb.style.overflow = "";
      f.rt.ca.style.overflow = "auto";
      if (a.onComplete) {
        a.onComplete();
      }
    }});
  };
  NlsLightBox.$mouseDown = function(a, c) {
    var b = document;
    b.onmousemove = function(b) {
      NlsLightBox.$startDrag(b ? b : a);
    };
    b.onmouseup = function(b) {
      NlsLightBox.$endDrag(b ? b : a);
    };
    b.onselectstart = function() {
      return !1;
    };
    b.onmousedown = function() {
      return !1;
    };
    b.ondragstart = function() {
      return !1;
    };
    NlsLightBox.trgElm = b.getElementById(c);
    NlsLightBox.gstElm = b.getElementById("dd$" + c);
    NlsLightBox.gstElm.style.top = NlsLightBox.trgElm.style.top;
    NlsLightBox.gstElm.style.left = NlsLightBox.trgElm.style.left;
    NlsLightBox.gstElm.style.width = NlsLightBox.trgElm.style.width;
    NlsLightBox.gstElm.style.height = NlsLightBox.trgElm.style.height;
    NlsLightBox.gstElm.style.display = "block";
    NlsLightBox.gstElm.style.zIndex = 999999;
    NlsLightBox.posDif = {x:a.clientX - parseInt(NlsLightBox.trgElm.style.left, 10), y:a.clientY - parseInt(NlsLightBox.trgElm.style.top, 10)};
  };
  NlsLightBox.$startDrag = function(a) {
    NlsLightBox.gstElm.style.left = a.clientX - NlsLightBox.posDif.x + "px";
    NlsLightBox.gstElm.style.top = a.clientY - NlsLightBox.posDif.y + "px";
    NlsLightBox.trgElm.style.left = a.clientX - NlsLightBox.posDif.x + "px";
    NlsLightBox.trgElm.style.top = a.clientY - NlsLightBox.posDif.y + "px";
  };
  NlsLightBox.$endDrag = function(a) {
    NlsLightBox.gstElm.style.display = "none";
    document.onmousemove = null;
    document.onmouseup = null;
    document.onmousedown = function() {
      return !0;
    };
    document.onselectstart = function() {
      return !0;
    };
    document.onselectstart = function() {
      return !0;
    };
  };
  NlsLightBox.getCenterXY = function(a, c) {
    var b = NlsLightBox.getClientSize(), d = parseInt(a, 10), e = parseInt(c, 10), d = isNaN(d) ? 0 : d > b.w ? b.w : d, e = isNaN(e) ? 0 : e > b.h ? b.h : e;
    return {x:(b.w - d) / 2, y:(b.h - e) / 2};
  };
  NlsLightBox.getClientSize = function() {
    return {w:window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth, h:window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight};
  };
  NlsLightBox.getScrollXY = function() {
    return {x:window.scrollX || document.body.scrollLeft || document.documentElement.scrollLeft, y:window.scrollY || document.body.scrollTop || document.documentElement.scrollTop};
  };
  NlsLightBox.fitToClient = function(a, c) {
    var b = a.width, d = a.height, e = NlsLightBox.getClientSize();
    e.h += c.avH;
    e.w += c.avW;
    b > e.w && (a.width = e.w, a.height = e.w * d / b);
    d > e.h && (a.height = e.h, a.width = e.h * b / d);
    return a;
  };
  NlsLightBox.createRequest = function() {
    if ("undefined" != typeof XMLHttpRequest) {
      return new XMLHttpRequest;
    }
    for (var a = ["MSXML2.XMLHttp.5.0", "MSXML2.XMLHttp.4.0", "MSXML2.XMLHttp.3.0", "MSXML2.XMLHttp", "Microsoft.XMHttp"], c = null, b = 0;b < a.length;b++) {
      try {
        return c = new ActiveXObject(a[b]);
      } catch (d) {
      }
    }
  };
  NlsLightBox.moveChilds = function(a, c) {
    for (var b = a.getElementsByTagName("input"), d = {}, e = 0;e < b.length;e++) {
      "radio" == b[e].type && b[e].checked && (d[b[e].name] = b[e].value);
    }
    for (e = 0;e < a.childNodes.length;) {
      c.appendChild(a.childNodes[e]);
    }
    b = c.getElementsByTagName("input");
    for (e = 0;e < b.length;e++) {
      "radio" == b[e].type && (b[e].checked = d[b[e].name] == b[e].value);
    }
  };
  NlsLightBox.$ = function(a) {
    if (document.getElementById) {
      return document.getElementById(a);
    }
    if (document.all) {
      return document.all(a);
    }
  };
  NlsLightBox.$PD = function(a, c) {
    for (var b = a, d = y = w = h = 0, e = {x:0, y:0};b;) {
      d += b.offsetLeft, y += b.offsetTop, b = b.offsetParent;
    }
    w += a.offsetWidth;
    h += a.offsetHeight;
    c || (e = NlsLightBox.getScrollXY());
    return {left:d - e.x, top:y - e.y, width:w, height:h};
  };
  NlsLightBox.$crtElement = function(a, c, b) {
    a = document.createElement(a);
    for (var d in c) {
      a[d] = c[d];
    }
    for (d in b) {
      a.style[d] = b[d];
    }
    return a;
  };
  NlsLightBox.objs[this.id] = this;
  return this;
}
NlsLightBox.objs = [];
