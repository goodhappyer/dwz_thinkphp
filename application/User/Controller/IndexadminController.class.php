<?php

/**
 * 会员
 */
namespace User\Controller;

use Common\Controller\AdminbaseController;

class IndexadminController extends AdminbaseController {
	/* 用户列表 */
	function index() {
		if (IS_POST) {
			if ($_POST ['keyword']) {
				$map ['user_login|user_nicename|user_email|mobile|hospital|offices|addr'] = array (
						'like',
						'%' . $_POST ['keyword'] . '%',
						'OR' 
				);
			}
		}
		$map['user_type']=2;
		$users_model = M ( "Users" );
		$count = $users_model->where ( array (
				"user_type" => 2 
		) )->count ();
		$page = $this->page ( $count, 20 );
		$lists = $users_model->where ($map)->order ( "create_time DESC" )->limit ( $page->firstRow . ',' . $page->listRows )->select ();
		$this->assign ( 'lists', $lists );
		$this->assign ( "page", $page->show ( 'Admin' ) );
		
		$this->display ( ":index" );
	}
	
	/* 添加用户 */
	function add() {
		$users_model = M ( "Users" );
		if (IS_POST) {
			if (empty ( $_POST ['password'] )) {
				$this->error ( "新密码不能为空！" );
			}
			$_POST ['user_nicename'] = $_POST ['user_login'];
			$_POST ['user_pass'] = sp_password ( $password );
			if ($users_model->create ()) {
				$result = $users_model->add ();
				if ($result !== false) {
					$this->success ( "添加成功！", U ( "user/indexadmin/index" ) );
				} else {
					$this->error ( "添加失败！" );
				}
			} else {
				$this->error ( $users_model->getError () );
			}
		} else {
			$this->display ( ":add" );
		}
	}
	
	/* 添加用户 */
	function edit() {
		$users_model = M ( "Users" );
		if (IS_POST) {
			
			if (! empty ( $_POST ['ol_user_pass'] )) {
				$_POST ['user_pass'] = sp_password ( $_POST ['ol_user_pass'] );
			}
			
			if ($users_model->create ()) {
				$result = $users_model->where ( 'id=' . $_GET ['id'] )->save ();
				if ($result !== false) {
					$this->success ( "编辑成功！", U ( "user/indexadmin/index" ) );
				} else {
					$this->error ( "编辑失败！" );
				}
			}
		} else {
			$info = $users_model->where ( array (
					'id' => $_GET ['id'] 
			) )->find ();
			$this->assign ( 'info', $info );
			$this->display ( ":edit" );
		}
	}
	
	/* 删除用户 */
	function delete() {
		if (M ( "Users" )->where ( 'id=' . $_GET ['id'] )->delete ()) {
			$this->success ( "删除成功！" );
		} else {
			$this->error ( "删除失败！" );
		}
	}
	function ban() {
		$id = intval ( $_GET ['id'] );
		if ($id) {
			$rst = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_status', '0' );
			if ($rst) {
				$this->success ( "会员拉黑成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员拉黑失败！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
	function cancelban() {
		$id = intval ( $_GET ['id'] );
		if ($id) {
			$rst = M ( "Users" )->where ( array (
					"id" => $id,
					"user_type" => 2 
			) )->setField ( 'user_status', '1' );
			if ($rst) {
				$this->success ( "会员启用成功！", U ( "indexadmin/index" ) );
			} else {
				$this->error ( '会员启用失败！' );
			}
		} else {
			$this->error ( '数据传入失败！' );
		}
	}
}
