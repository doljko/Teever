<div class="col-md-4 short-story">
    <a href="{full-link}">
        <div  class="short-story-img-wrap">
            <div style="background:url('{image-1}')" class="short-story-img-bg">
            </div>

        </div>
    </a>
    <a href="{full-link}" class="short-story-title">{title}</a>
    <div class="short-story-desc">{category}</div>
</div>
<style>
    .short-story{
    	margin-bottom:30px;
        transition: all .3s ease-in-out
    }
    .short-story-img-wrap{
    	margin-bottom:11px;
        height: 200px;
        width: 100%;
        overflow:hidden;
    }
    .short-story-img-bg{
        height: 200px;
        width: 100%;
    	background-size:cover !important;
        background-position:50% !important;
        transition: transform 0.3s ease-in;
        -webkit-transition: transform 0.3s ease-in;
       -moz-transition: transform 0.3s ease-in;
            transition: transform 0.3s ease-in;
    }
    .short-story-img-bg:hover{
        transform:scale(1.1)
    }
    .short-story img{
    	width:100%;
        height:auto;
    }
    .short-story-title{
    	display:block;
        font-weight:normal;
        font-size:15px;
        line-height:22px;
        color: #9eca45;
        text-align:center;
        height:44px;
        overflow:hidden;
    }
    .short-story-desc{
    font-size:12px;
    	line-height:16px;
        height:16px;
        overflow:hidden;
color: #b2b2b2;
text-align: center;
    }
</style>