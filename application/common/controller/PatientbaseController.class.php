<?php
namespace Common\Controller;
use Common\Controller\AppframeController;
class PatientbaseController extends AppframeController {
	
	public function __construct() {
		$this->set_action_success_error_tpl();
		parent::__construct();
	}
	function _initialize() {
		parent::_initialize();
		$site_options=get_site_options();
		$this->assign($site_options);
		$ucenter_syn=C("UCENTER_ENABLED");
	}	
}