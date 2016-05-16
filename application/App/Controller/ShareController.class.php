<?php

namespace App\Controller;

use Common\Controller\AppBaseController;

/**
 *  分享回调
 */
class ShareController extends AppBaseController {
	public function _initialize()
	{
			parent::_initialize ();
	}
	//分享回调
	public function add()
	{
		$share=D("share");
		$d['share_to']=I("share_to");
		$d['dir']=I("dir");
		$d['user_id']=$this->user_info['id'];
		$patient=D("patient");
		$r=$patient->where(array("dir"=>$d['dir']))->find();
		if(!$r)
		{
			$this->json_echo(0,"分享目录失效！");
		}
		$d['patient_id']=$r['id'];//患者id
		$d['input_time']=time();
		if($share->add($d))
		{
			$this->json_echo(1,"分享成功！");
		}
		else
		{
			$this->json_echo(0,"分享失败！");
		}
	}
}


