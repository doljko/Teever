jQuery(document).ready(function($){$('ul.fa-ul text-s >li').prepend('<i class="fa-li fa fa-angle-double-right"></i>');$(' input[type=search]').attr('placeholder','Search for');$('.class-simplenews input[type=email]').attr('placeholder','Email');$('.class_full >div').attr('class','overlaybox');$('.class_full2  > .bg-color >div').attr('class','bg-overlay dotted');$('.class_masonry:nth-child(2n+1) > .maso-item ').attr('class','maso-item col-md-8');$('.class_masonry:nth-child(2n) > .maso-item ').attr('class','maso-item col-md-4');$('.class_side > a').append('<span class="fa arrow"></span>');$('.class_side2 > a').append('<span class="fa plus-times"></span>');$('.class_side3 > a').append('<span class="fa plus-times"></span>');$('.menu_icon > a >i').attr('class','anima icon onlycover class_icon');$('.menu_icon2 > a >i').attr('class','anima icon onlycover class_icon2');$('.menu_icon3 > a >i').attr('class','anima icon onlycover class_icon3');$('.menu_icon4 > a >i').attr('class','anima icon onlycover class_icon4');$('.menu_icon5 > a >i').attr('class','anima icon onlycover class_icon5');$('.menu_icon6 > a >i').attr('class','anima icon onlycover class_icon6');$('.menu_icon7 > a >i').attr('class','anima icon onlycover class_icon7');});$(document).ready(function(){$('#anima-layer-a2').pan({fps:30,speed:0.7,dir:'left',depth:30});$('#anima-layer-b2').pan({fps:30,speed:1.2,dir:'left',depth:70});});$(document).ready(function(){$(".popup-trigger").click(function(){$(".popup-banner").hide();});});