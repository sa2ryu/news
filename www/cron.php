<?php
//include_once(__DIR__ . '/lib/facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php');
include_once(__DIR__.'/lib/simplepie-1.4.3/autoloader.php');
$source = parse_ini_file(__DIR__.'/source.ini',true);
//$fbapi    = 'http://graph.facebook.com/?id=';
$blogs = [];
$unique   = [];
// データの取得
foreach($source as $k => $v){
    $url = $v['url'];
    $feed = new SimplePie;
    $feed->enable_cache(false);
    $feed->set_feed_url($url);
    $feed->init();
    if($feed->error()){
        var_dump($feed->error());
        continue;
    }
    foreach($feed->get_items() as $item){
        $cc = new content($item);
        // 24時間以内のデータのみ
        $date = strtotime($item->get_date('Y-m-d H:i:s'));
        if($date < time() - 24*60*60){
            continue;
        }
        // urlの重複を避ける
        if(!$item->get_link() || in_array($item->get_link(),$unique)){
            continue;
        }
        $unique[]   = $item->get_link();
        $tmp = $cc->getAll();
        $tmp['source'] = $k;
        if(isset($v['tag'])){
            $tmp['tag'] = $v['tag'];
        }
        $blogs[] = $tmp;
    }
}
// シェア数の一括取得
/*
array_walk($unique,function(&$v,$k,$fbapi){ $v = $fbapi.$v; },$fbapi);
$curl = new curl;
$curl->setUrl($unique);
$curl->exec();
$res = $curl->getRes();
$res = array();
foreach($unique as $i => $v){
	$res[$i] = array();
	$res[$i]['fb'] = file_get_contents($fbapi.$v);
//	$res[$i]['tw'] = file_get_contents($twapi.$v);
}
*/
/*
$tmp = array();
foreach($blogs as $i => $v){
	$fbc = json_decode($res[$i]['fb']);
	$tmp[$i] = $v;
	$tmp[$i]['fb'] = isset($fbc->share->share_count)?(int)$fbc->share->share_count:0;
	$tmp[$i]['ttl'] = $tmp[$i]['fb'];
}
$blogs = $tmp;
// total順にソート
usort($blogs,function($a,$b){
    return $a['ttl'] < $b['ttl'];
});
if($blogs[0]['ttl'] == 0){
    var_dump('total 0 error');
    exit();
}
*/
// 日付順にソート
usort($blogs,function($a,$b){
    return $a['date'] < $b['date'];
});

// データファイルに保存
file_put_contents(dirname(__FILE__).'/blogs.txt',serialize($blogs));
// ログの保存 一日一回最初だけ
/*
$log = dirname(__FILE__).'/log/'.date('Y-m-d').'.txt';
if(file_exists($log)){
	file_put_contents($log,serialize($blogs));
}
*/

// {{{ class content
class content{
    public $c = array();
    public function __construct($content){
        $this->c = $content;
    }
    public function getAll(){
        $tmp = array(
                'title' => $this->c->get_title(),
                'link'  => $this->c->get_link(),
                'date'  => strtotime($this->c->get_date('Y-m-d H:i:s')),
                'img'   => $this->getImgUrl(),
                );
        return $tmp;
    }
    public function getImgUrl(){
        $pattern = '@<img.+?src="(http://.+?)"(.*?)>@';
        preg_match_all($pattern, $this->c->get_content(), $m);
        foreach($m[1] as $w){
            if(isset($w)&&!inStr($w,array('hatena','rss'))){
                return $w;
            }
        }
        return sprintf('http://s.wordpress.com/mshots/v1/%s?w=320',urlencode($this->c->get_link()));
    } 
}
// }}}

// 配列から文字列の検索
function inStr($str,$needle){
    foreach((array)$needle as $v){
        if(strpos($str,$v)!==false){
            return true;
        }
    }
    return false;
}

?>
