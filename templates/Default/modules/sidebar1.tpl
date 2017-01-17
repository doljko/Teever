<div class="col-md-3">

	<div class="sidebar_widget">
    
    	<div class="sidebar_title"></div>
		<ul class="arrows_list1">		
            {custom category="1" template="modules/sidebar1_navigation1" limit="10" from="0" order="1" cache="yes" avaible="global"}
            {custom category="2" template="modules/sidebar1_navigation1" limit="10" from="0" order="1" cache="yes" avaible="global"}
		</ul>
        
	</div><!-- end section -->
    
    <div class="margin_top4"></div>
    <div class="clearfix"></div>
    
    <div class="sidebar_widget">
    
    	<div class="sidebar_title"><h4>Мэдээ</h4></div>
        
			<ul class="recent_posts_list">
                
                {custom category="1" template="modules/sidebar1_news" limit="3" from="0" order="date" cache="yes" avaible="global"}

            </ul>
                
	</div>
    
    <div class="clearfix margin_top4"></div>
    

    <div class="sidebar_widget">
        
			<ul class="adsbanner-list">  
            	<li><a href="#"><img src="{THEME}/images/banner/sample-ad-banner.jpg" alt=""></a></li>
            </ul>
            
	</div><!-- end section -->
    
    <div class="clearfix margin_top4"></div>
    
    <div class="sidebar_widget">
    
    	<div class="sidebar_title"><h4>Архив</h4></div>
        
		<ul class="list2">		
            <li><a href="#"><i class="fa fa-long-arrow-right"></i> 2016 оны 12 сар</a></li>
            <li><a href="#"><i class="fa fa-long-arrow-right"></i> 2016 оны 11 сар</a></li>
            <li><a href="#"><i class="fa fa-long-arrow-right"></i> 2016 оны 10 сар</a></li>
            <li><a href="#"><i class="fa fa-long-arrow-right"></i> 2016 оны 9 сар</a></li>
            <li><a href="#"><i class="fa fa-long-arrow-right"></i> 2016 оны 8 сар</a></li>
		</ul>
        
	</div><!-- end section -->
    
	
</div>
<style>
    /* sidebar widget */
    .adsbanner-list li {
    margin: 0px;
    padding: 0px;
    float: left;
    width: 100%;
    height: 100%;
    list-style-type: none;
}
    .adsbanner-list li img{
    	width:100%;
        height:auto;
    }
.sidebar_widget {
	float: left;
	width: 100%;
	padding: 0px;
	margin: 0px;
}
.sidebar_widget ul.arrows_list1{
	padding: 0px;
	margin: 0px;
	float: left;
	margin-top: -10px;
}
.sidebar_widget ul.arrows_list1 li a {
	color: #9eca45;
	line-height: 30px;
}
.sidebar_widget ul.arrows_list1 li a:hover {
	color: #272727;
}
.sidebar_widget ul.arrows_list1 i {
	margin-right: 4px;
}
.sidebar_title {
	float: left;
	width: 100%;
}
.sidebar_widget h4, .clientsays_widget h4 {
	margin-bottom: 30px;
	float: left;
	font-weight: 500;
}

/* Recent Posts */
ul.recent_posts_list {
	margin: 0px;
	padding: 0px;
	width: 100%;
	float: left;
}
.recent_posts_list li {
	padding: 0px 0px 13px 0px;
	margin: 0px 0px 16px 0px;
	list-style-type: none;
	border-bottom: 1px solid #eee;
	float: left;
	width: 100%;
}
.recent_posts_list li a {
	text-decoration: none;
	line-height: 17px;
	display: block;
	color: #999;
}
.recent_posts_list li a:hover {
	color: #9eca45;
}

.recent_posts_list li span {
	float: left;
	margin-right: 15px;
}
.recent_posts_list li span img {
    width:60px;
    height:auto;
	float: left;
	margin-right: 0px;
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	-moz-opacity: 1;
	-khtml-opacity: 1;
	opacity: 1;
}
.recent_posts_list li span img:hover {
	float: left;
	margin-right: 0px;
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
	-moz-opacity: 0.8;
	-khtml-opacity: 0.8;
	opacity: 0.8;
}
.recent_posts_list li i {
	padding: 1px 0px 0px 0px;
	margin: 0px;
	display: block;
	font-size: 10px;
	font-style: normal;
	color: #c9c9c9;
}
.recent_posts_list li.last {
	padding: 0px 0px 7px 0px;
	margin: 0px 0px 0px 0px;
	border-bottom: 0px solid #f5f6f6;
}

/* client says widget */
.clientsays_widget {
	float: left;
	width: 100%;
	padding: 0px;
	margin: 0px;
	color: #999;
}
.clientsays_widget strong {
	font-weight: 600;
	color: #454545;
}
.clientsays_widget h3 {
	margin-bottom: 18px;
}
.clientsays_widget img {
	float: left;
	margin-right: 13px;
	margin-top: 7px;
}
</style>