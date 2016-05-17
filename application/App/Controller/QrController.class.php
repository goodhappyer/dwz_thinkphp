<?php

namespace App\Controller;

use Common\Controller\AppBaseController;

class QrController extends AppBaseController {
	function _initialize() {
		parent::_initialize ();
	}
	
	/**
	 * 患者信息二维码回调
	 * 
	 * @param $dir 文件目录        	
	 */
	public function callback($dir) {
		$map ['dir'] = $dir;
		$patient_model = M ( 'patient' );
		if ($data ['uid'] = $this->user_info ['id']) {
			
			if ($info = $patient_model->where ( $map )->find ()) {
				if (empty ( $info ['uid'] )) {
					$patient_model->where ( $map )->save ( $data ['uid'] ); // 保存上传所有者
				} else {
					$arr ['patient_uid'] = $info ['uid'];
					$arr ['share_uid'] = $data ['uid'];
					M ( 'patient_share' )->add ();
				}
			}
		} else {
		}
		
		// 获取用户ID;
		$data ['uid'] = $this->user_info ['id'];
		
		$map ['dir'] = $dir;
		$patient_model = M ( 'patient' );
		if ($info = $patient_model->where ( $map )->find ()) {
			if (empty ( $info ['uid'] )) {
				$patient_model->where ( $map )->save ( $data ['uid'] ); // 保存上传所有者
			} else {
			}
		}
	}
}