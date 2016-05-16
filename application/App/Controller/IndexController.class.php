<?php

namespace App\Controller;

use Common\Controller\AppBaseController;

/**
 * 测试
 */
class IndexController extends AppBaseController {
	// 测试
	public function index() {
		print_r(json_decode($this->sendTemplateSMS ( "13835021859", "1112", "19028" ),true)) ;
	}
}


