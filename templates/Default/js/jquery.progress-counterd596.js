(function(n){function i(n,t){return n.toFixed(t.decimals)}var t=function(i,r){this.$element=n(i);this.options=n.extend({},t.DEFAULTS,this.dataOptions(),r);this.init()};t.DEFAULTS={from:0,to:0,speed:1e3,refreshInterval:100,decimals:0,formatter:i,onUpdate:null,onComplete:null};t.prototype.init=function(){this.value=this.options.from;this.loops=Math.ceil(this.options.speed/this.options.refreshInterval);this.loopCount=0;this.increment=(this.options.to- this.options.from)/ this.loops }; t.prototype.dataOptions = function () { var n = { from: this.$element.data("from"), to: this.$element.data("to"), speed: this.$element.data("speed"), refreshInterval: this.$element.data("refresh-interval"), decimals: this.$element.data("decimals") }, i = Object.keys(n), r, t; for (r in i) t = i[r], typeof n[t] == "undefined" && delete n[t]; return n }; t.prototype.update = function () { this.value += this.increment; this.loopCount++; this.render(); typeof this.options.onUpdate == "function" && this.options.onUpdate.call(this.$element, this.value); this.loopCount >= this.loops && (clearInterval(this.interval), this.value = this.options.to, typeof this.options.onComplete == "function" && this.options.onComplete.call(this.$element, this.value)) }; t.prototype.render = function () { var n = this.options.formatter.call(this.$element, this.value, this.options); this.$element.text(n) }; t.prototype.restart = function () { this.stop(); this.init(); this.start() }; t.prototype.start = function () { this.stop(); this.render(); this.interval = setInterval(this.update.bind(this), this.options.refreshInterval) }; t.prototype.stop = function () { this.interval && clearInterval(this.interval) }; t.prototype.toggle = function () { this.interval ? this.stop() : this.start() }; n.fn.countTo = function (i) { return this.each(function () { var u = n(this), r = u.data("countTo"), f = !r || typeof i == "object", e = typeof i == "object" ? i : {}, o = typeof i == "string" ? i : "start"; f && (r && r.stop(), u.data("countTo", r = new t(this, e))); r[o].call(r) }) } })(jQuery);
if(!isEmpty(optionsString)){optionsArr=optionsString.split(",");options=getOptionsString(optionsString,options);}
$(this).circleProgress(options);var inner=$(this).find(".inner-circle .counter-circle");if(inner){$(this).find(".inner-circle").css("display","table")
$(this).on('circle-animation-progress',function(event,progress,stepValue){$(inner).html(parseInt(stepValue.toFixed(2)*100,10)+" %");});}}}(jQuery));