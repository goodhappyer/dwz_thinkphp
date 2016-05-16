<?php

namespace App\Controller;

use Think\Controller;

/**
 * 不需要用户的控制器,新闻列表
 */
class NewsController extends Controller {
	
	/**
	 * APP 首页
	 */
	public function index() {
		if (empty ( $_GET ['p'] )) {
			$_GET ['p'] = 1;
		}
		
		$term_relationships=D("term_relationships");
		$r=$term_relationships->where(array("status"=>0))->field("object_id")->select();
		$T=array();
		foreach ($r as $v)
		{
			$T[]=$v['object_id'];
		}
		$ids=implode(",", $T);
		$data = M ( "posts_views" )->field ( "id,post_author,post_keywords,post_source,post_date,post_title,post_excerpt,post_status,comment_status,post_modified,post_content_filtered,post_parent,post_type,post_mime_type,comment_count,smeta,post_hits,post_like,istop,recommended,name,description" )->where ( array (
				"name" => "首页新闻" ,'id'=>array("NOT IN",$ids)
		) )->page ( $_GET ['p'] . ',10' )->select ();
		if ($data) {
			foreach ( $data as $k => $v ) {
				$data [$k] ['url'] = U ( 'Don/winfo', array (
						'id' => $v ['id'] 
				), true, true );
				if (mb_strlen ( $data [$k] ['post_title'] ) > 20) {
					$data [$k] ['post_title'] = mb_substr ( $data [$k] ['post_title'], 0, 20, 'utf-8' ) . "...";
				}
				$photo = json_decode ( $data [$k] ['smeta'], true );
			}
			
			$this->json_echo ( 1, '获取成功', $data,1 );
		} else {
			$this->json_echo ( 0, '获取失败', $data );
		}
	}
	
	/**
	 * 文章详情
	 */
	public function show() {
		$map [id] = I ( 'id' );
		if ($info = M ( 'posts' )->where ( $map )->find ()) {
			html5 ( $info ['post_content'], $info ['post_title'] );
		} else {
			json_echo ( 0, '获取失败' );
		}
	}
}


