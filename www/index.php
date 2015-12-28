<?php
$tmp = file_get_contents(dirname(__FILE__).'/blogs.txt');
$blogs = unserialize($tmp);
$tmp = array();
$tagl = array('life','tech','game','neta');
// タグでの絞込
$tag = isset($_GET['t'])?$_GET['t']:false;
if($tag){
    foreach($blogs as $v){
        if(in_array($tag,$v['tag'])){
            $tmp[] = $v;
        }
    }
    $blogs = $tmp;
}
?>
<html>
<head>
<title>わくぶるにゅーす</title>
<meta name="viewport" content="width=device-width"/>
<meta name="date" content="<?php echo date(DATE_ISO8601,strtotime(date('Y-m-d H:00:00'))); ?>" />
<meta name="description" content="わくぶるするニュースを集めています。"/>
<meta name="keywords" content="facebook,ランキング,ニュース"/>
<meta property="og:title" content="わくぶるにゅーす" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://antenna.lant.jp" />
<meta property="og:image" content="" />
<meta property="og:site_name" content="わくぶるにゅーす" />
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="">
<meta name="twitter:title" content="わくぶるにゅーす">
<meta name="twitter:description" content="わくぶるするニュースを集めています。">
<meta name="twitter:creator" content="@wkwkan">
<meta name="twitter:image:src" content="">
<meta name="twitter:domain" content="news.wkbr.net/">
<link rel="stylesheet" href="/static/css/default.css" type="text/css" />
<link rel="canonical" href="http://news.wkbr.net/">
</head>

<body>
<div id="wrap">

<div id="head">
<h1><a href="/">わくぶるにゅーす</a></h1>
<div id="nav">
<ul>
<li><a href="#" class="navImage">画像ビュー</a></li>
<li><a href="#" class="navList">リストビュー</a></li>
</ul>
</div>
</div>

<div id="taglist">
<ul>
<li><a href="/">Top</a></li>
<?php foreach($tagl as $v): ?>
<li><a href="/t/<?php echo $v; ?>"><?php echo ucfirst($v); ?></a></li>
<?php endforeach; ?>
</ul>
</div>

<div id="container" class="typeImage">
<?php foreach($blogs as $i => $v): ?>
<div class="out">
<div class="box" style="background-image: url('<?php echo $v['img'];?>');">
<div class="top">
<span class="fb"><?php echo $v['fb'];?></span>
<?php /*
<span class="tw"><?php echo $v['tw'];?></span>
*/ ?>
</div>
<div class="bottom">
<a href="<?php echo $v['link'];?>" target="_brank">
<p><?php echo $v['title'];?></p>
<time class="date"><?php echo date('Y/m/d G:i',$v['date']);?></time>
</a>
</div>
</div>
</div>
<?php
endforeach;
?>
</div>
</div>


<div id="foot">
<p>情報提供,相互リンクお待ちしております。</p>
<iframe src="https://docs.google.com/forms/d/13U4l6cxZp5p4cR5awuTyadQ957CAgwBJubM3rTwCHyU/viewform?embedded=true">読み込み中...</iframe>
</div>
</div>
<script src="/static/js/jquery-2.1.0.min.js"></script>
<script>
jQuery(function($){
$(window).on('load resize', function(){
var conwidth = $('.typeImage').width();
var defaultboxwidth = 270;
var boxcount = parseInt(conwidth / defaultboxwidth);
var boxwidth = parseInt(conwidth / boxcount) - 18;
$('.typeImage .box').width(boxwidth);
$('.typeList .box').width('');
});
});
</script>
<script>
jQuery(function($){
	var $container = $("#container"),
		$navImage = $(".navImage"),
		$navList  = $(".navList");
	$navImage.on("click", function(e){
		e.preventDefault();
		$container.removeClass("typeList").addClass("typeImage");
	});
	$navList.on("click", function(e){
		e.preventDefault();
		$container.removeClass("typeImage").addClass("typeList");
		$('.typeList .box').width('');
	});
});
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-56794611-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>
