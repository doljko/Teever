<!-- Slider
======================================= --> 
<link rel="stylesheet" href="{THEME}/css/slider1_style.css" type="text/css" />
<link rel="stylesheet" href="{THEME}/css/slider1_masterslider.css" type="text/css" />
<!-- masterslider -->
<div class="slidermar">
<div class="master-slider ms-skin-default" id="masterslider">

    {custom category="1" template="modules/slider1_short1" limit="1" from="0" order="date" cache="yes" avaible="global"}
    {custom category="1" template="modules/slider1_short2" limit="1" from="1" order="date" cache="yes" avaible="global"}
    {custom category="1" template="modules/slider1_short3" limit="1" from="2" order="date" cache="yes" avaible="global"}

</div>
</div>
<!-- end of masterslider -->
<script src="{THEME}/js/slider1_masterslider.min.js"></script>
<script type="text/javascript">
(function($) {
 "use strict";
	var slider = new MasterSlider();
	// adds Arrows navigation control to the slider.
	slider.control('arrows');
	slider.control('bullets');
	
	slider.setup('masterslider' , {
		 width:1400,    // slider standard width
		 height:680,   // slider standard height
		 space:0,
		 speed:45,
		 layout:'fullwidth',
		 loop:true,
		 preload:0,
		 autoplay:true,
		 view:"parallaxMask"
	});
})(jQuery);
</script>
