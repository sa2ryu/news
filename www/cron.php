<?php
include_once(dirname(__FILE__).'/lib/magpierss-0.72/rss_fetch.inc');
include_once(dirname(__FILE__).'/lib/magpierss-0.72/rss_utils.inc');
//include_once(dirname(__FILE__).'/lib/Curl.class.inc');
$source = parse_ini_file(dirname(__FILE__).'/source.ini',true);
$fbapi    = 'http://graph.facebook.com/?id=';
//$twapi    = 'http://urls.api.twitter.com/1/urls/count.json?url=';
$blogs = array();
$unique   = array();
// データの取得
foreach($source as $v){
    $url = $v['url'];
    $rss = fetch_rss($url);
    if(!$rss){
        var_dump($v);
        continue;
    }
    foreach($rss->items as $content){
        $cc = new content($content);
        // 24時間以内のデータのみ
        if($cc->getDate() < time() - 24*60*60){
            continue;
        }
        // urlの重複を避ける
        if(!$cc->getLink() || in_array($cc->getLink(),$unique)){
            continue;
        }
        $unique[]   = $cc->getLink();
        $tmp = $cc->getAll();
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
*/
$res = array();
foreach($unique as $i => $v){
	$res[$i] = array();
	$res[$i]['fb'] = file_get_contents($fbapi.$v);
//	$res[$i]['tw'] = file_get_contents($twapi.$v);
}
$tmp = array();
foreach($blogs as $i => $v){
	$fbc = json_decode($res[$i]['fb']);
//	$twc = json_decode($res[$i]['tw']);
	$tmp[$i] = $v;
	$tmp[$i]['fb'] = isset($fbc->shares)?(int)$fbc->shares:0;
//	$tmp[$i]['tw'] = isset($twc->count)?(int)$twc->count:0;
//	$tmp[$i]['ttl'] = $tmp[$i]['fb'] + $tmp[$i]['tw'];
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
    public $content = array();
    public function __construct($content){
        $this->content = $content;
    }
    public function getAll(){
        $tmp = array(
                'title' => htmlspecialchars($this->get('title')),
                'link'  => strip_tags($this->getLink()),
                'date'  => $this->getDate(),
                'img'   => strip_tags($this->getImgUrl()),
                );
        return $tmp;
    }
    public function getImgUrl(){
        $tmp = array('description','content/encoded');
        foreach($tmp as $v){
            $pattern = '@<img.+?src="(http://.+?)"(.*?)>@';
            preg_match_all($pattern, xpath($this->content,$v), $m);
            foreach($m[1] as $w){
                if(isset($w)&&!inStr($w,array('hatena','rss'))){
                    return $w;
                }
            }
        }
        return sprintf('http://s.wordpress.com/mshots/v1/%s?w=320',urlencode($this->getLink()));
    }
    public function getDate(){
        $tmp = array('date','pubdate','dc/date','issued');
        foreach($tmp as $v){
            $c = xpath($this->content,$v);
            if($c){
                return strtotime($c);
            }
        }
    }
    public function getLink(){
        $tmp = array('link','guid');
        $through = array('headlines.yahoo.co.jp',
                'youtube.com',
                'groups.google.com/forum',
                'facebook.com'
                );
        foreach($tmp as $v){
            $c = xpath($this->content,$v);
            if(strrpos($c,'?')&&!inStr($c,$through)){
                $c = substr($c,0,strrpos($c,'?'));
            }
            if($c && !strpos($c,'rss')){
                return $c;
            }
        }
    }
    public function get($param){
        //return isset($this->content[$param])?$this->content[$param]:'';
        return $this->content[$param];
    }
}
// }}}

// {{{ function
// 配列にpathでアクセス
function xpath($array,$path){
    if(!is_array($array)){ return false; }
    foreach(explode('/',$path) as $v){
        if(!isset($array[$v])){
            return false;
        }
        $array = $array[$v];
    }
    return $array; 
}
// 配列から文字列の検索
function inStr($str,$needle){
    foreach((array)$needle as $v){
        if(strpos($str,$v)!==false){
            return true;
        }
    }
    return false;
}
// }}}

?>
