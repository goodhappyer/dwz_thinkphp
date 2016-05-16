<?php
namespace App\Controller;
use Common\Controller\AppBaseController;

class DataController extends AppBaseController
{

	public function _initialize ()
	{
		parent::_initialize();
	}

	/**
	 * 我的数据
	 */
	public function Mydata ()
	{
		if (empty($_GET['p']))
		{
			$_GET['p'] = 1;
		}
		$uid = $this->user_info['id'];
		$data = M('Data')->where(array ('uid' => $uid))
			->order("collect asc")
			->order("create_time desc")
			->page($_GET['p'] . ',10')
			->select();
		if ($data)
		{
			$this->json_echo(1,'获取成功',$data);
		}
		else
		{
			$this->json_echo(1,'无数据');
		}
	}
	/* 播放时需要的数据 */
	public function show ()
	{
		$id = I('id');
		if (! is_numeric($id))
		{
			$this->json_echo(0,'参数错误！');
		}
		$r = M('Data')->where(array ("id" => $id))->find();
		$r['Datatitle'] = D('Datatitle')->where(array ('data_id' => $id))->find();
		$r['Datatitle']['items'] = unserialize($r['Datatitle']['items']);
		$r['Datatitle']['filepath'] = C('SERVER_NAME') . '/' . $r['Datatitle']['filepath'] . "?token=" . I('token');
		$r['Eventlist'] = D('Eventlist')->where(array ('data_id' => $id))->find();
		$r['Testproject'] = D('Testproject')->where(array ('data_id' => $id))->select();
		$r['Videolist'] = D('Videolist')->where(array ('data_id' => $id))->select();
		foreach ($r['Videolist'] as $k => $v)
		{
			$r['Videolist'][$k]['mp4'] = C('SERVER_NAME') . '/' . $r['Videolist'][$k]['mp4'] . "?token=" . I('token');
		}
		$this->json_echo(1,'获取成功',$r);
	}
}


