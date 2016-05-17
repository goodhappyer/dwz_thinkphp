<?php

namespace App\Controller;

use Think\Controller;

/**
 *  单页内容
 */
class PageController extends Controller {
	/**
	 * 跟据标题输出一个HTML页面
	 */
	public function  index()
	{
		
		$post_title=trim(I("post_title"));
		
		$posts=D("Posts");
		
		$r=$posts->where(array("post_title"=>$post_title))->find();
		if($r)
		{
			echo $this->html5($r['post_content']);
		}
		else 
		{
			echo $post_title;
		}
	}
	// 输出一个html5页面
	function html5($content) {
		$str = '<!DOCTYPE HTML><html><head>';
		$str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Cache-Control" content="no-cache"/>
<meta name="viewport" content="width=device-width; initial-scale=1;  minimum-scale=1.0; maximum-scale=1.4"/>
<meta name="MobileOptimized" content="240"/>
<style>
p img,img{display:block;margin:0 auto; max-width:340px;vertical-align: middle;}
p{display:block;max-width:340px;}</style>
</head><body>';
		$str .= '<div>' . $content . "</div>";
		$str .= '</body></html>';
		return  $str;
	}
}