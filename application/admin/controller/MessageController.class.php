<?php

namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class MessageController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
    }

    /**
     *  显示列表
     */
    public function index() {

		$patient_model=M('message');

		$count=$patient_model->where($map)->count();
		$page = $this->page($count, 20);
		$lists = $patient_model->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

		$this->assign("page", $page->show('Admin'));
		$this->assign("lists",$lists);
        $this->display();
    }

    /**
     *  添加  发送消息
     */
    public function add() {

        if(IS_POST){

            if($_POST['send'] != 1){
                $_POST['send_time'] = time();
            }else{
                $_POST['send_time'] = strtotime($_POST['send_time']);
            }

            $message=M('message');
            if($message->create()){
                if($id=$message ->add()){

                    $arr=array(
                        'data'=>array(
                            'title'=>$_POST['title'],
                            'content'=>$_POST['describe'],
                            'info_url'=>U('app/Wap/pushInfo',array('id'=>$id),true,true),
                        )
                    );

                    if($_POST['push_type'] != 0)
                    {
                        $map['id|user_login|mobile']= array('in',$_POST['push_user']);
                        $users_id=M('users') ->field('id')->where($map)->select();
                        foreach($users_id as $k=>$v){
                            $users[$k]=$v['id'];
                        }
                    }
                    $data_id['push_user_id']=implode(',',$users_id);
                    $message ->where('id='.$id)->save($data_id);
                    if(msg_push($_POST['title'],$_POST['describe'],$arr,false,$users)){
                        $this ->success('发送成功');
                    }else{
                        $this ->error('发送失败');
                    }

                }
                else
                {
                    $this ->error('发送失败');
                }
            }

        }else{
            $this->display();
        }
    }


    /**
     *  删除
     */
    public function delete() {
        $id = intval($_GET['id']);
        if(M('message') ->where('id='.$id)->delete()){
            $this ->success('删除成功');
        }else{
            $this ->error('删除失败');
        }
    }

}

