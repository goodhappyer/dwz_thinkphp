<?php

namespace App\Controller;

use Common\Controller\AppBaseController;

/**
 * 
 */
class WapController extends AppBaseController {
	public function _initialize() {
		parent::_initialize ();
	}
	public function index()
	{
		echo "WAP网页";
	}

}


