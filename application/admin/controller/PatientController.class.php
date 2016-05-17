<?php
/**
 * Menu(患者管理)
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PatientController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
    }

    /**
     *  显示列表
     */
    public function index() {
		$patient_model=M('patient');

        if(IS_POST){
            if($_POST['start_time'] && $_POST['end_time']){
                $map['create_time'] = array('between',array(strtotime($_POST['start_time']),strtotime($_POST['end_time'])));
            }

            if($_POST['keyword']){
                $map['case_id|patient_id|patient_name|dir'] =  array('like','%'.$_POST['keyword'].'%','OR');
            }

        }

        $lists=$patient_model->where($map)->group('merge_num')->select();

        $page = $this->page(count($lists), 20);
        $lists=$patient_model->where($map)->group('merge_num')->limit($page->firstRow . ',' . $page->listRows)->order('create_time DESC')->select();

        $this->assign("Page", $page->show('Admin'));
		$this->assign("lists",$lists);
        $this->display();
    }


    public function indexGroup(){
        $patient_model=M('patient');

        if(IS_POST){
            if($_POST['start_time'] && $_POST['end_time']){
                $map['create_time'] = array('between',array(strtotime($_POST['start_time']),strtotime($_POST['end_time'])));
            }

            if($_POST['keyword']){
                $map['case_id|patient_id|patient_name|dir'] =  array('like','%'.$_POST['keyword'].'%','OR');
            }

        }
        $map['merge_num'] = I('merge_num');

        $count=$patient_model->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $patient_model->where($map)->limit($page->firstRow . ',' . $page->listRows)->order('create_time DESC')->select();

        $this->assign("Page", $page->show('Admin'));
        $this->assign("lists",$lists);
        $this->display();
    }



    public function indexRes(){
        $map['patient_dir'] = I('dir');
        $users_patient=M('users_patient');

        if(IS_POST){
            if($_POST['start_time'] && $_POST['end_time']){
                $map['A.update_time'] = array('between',array(strtotime($_POST['start_time']),strtotime($_POST['end_time'])));
            }

            if($_POST['keyword']){
                $map['B.user_nicename|B.mobile|B.hospital'] =  array('like','%'.$_POST['keyword'].'%','OR');
            }

        }


        $count=$users_patient->alias('A') ->join('__USERS__ as B ON A.users_id=B.id') ->where($map)->count();
        $page = $this->page($count, 20);

        $lists=$users_patient->alias('A') ->join('__USERS__ as B ON A.users_id=B.id') ->where($map)->order('A.create_time DESC')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("lists",$lists);
        $this ->display();
    }


    /**
     * 合并
     */
    public function merges(){
        $data['merge_num'] = $_POST['ids'][0];
        $map['merge_num'] = array('in',$_POST['ids']);
        if(M('patient') ->where($map)->save($data)){
            $this ->success('合并成功');
        }else{
            $this ->error('合并失败');
        }
    }

    /**
     * 拆分
     */
    public function splits(){
        foreach($_POST['ids'] as $k=>$vs){
            $data['merge_num'] = time()+mt_rand('0','100');
            $map['id'] = $vs;
            $res=M('patient') ->where($map)->save($data);

            if(!$res){
                $this ->error('拆分失败ID:'.$vs);
            }
        }

        $this ->success('拆分成功');


    }



    /**
     * 诊断结果
     */
    public  function results(){
        $map['patient_dir'] = I('dir');
        $lists=M('users_patient')->where($map)->select();
        $this->assign("lists",$lists);
        $this->display();
    }

    /**
     *  删除
     */
    public function delete() {
        $id = intval($_GET['id']);
        if(M('patient') ->where('id='.$id)->delete()){
            $path='Upload/Patient/'.$_GET['dir'];
            if(rmfile($path)){
                $this ->success('删除目录文件和数据成功');
            }else{
                $this ->error('数据删除成功.删除目录文件失败,请手动删除！'.$path);
            }
        }else{
            $this ->error('删除失败');
        }




    }

    /**
     *  更改路径
     */
    public function editPath(){
            $_POST['dir'] = date('Ymd_').mt_rand(0,999999);

            $url = U('App/wap/index',array('dir'=>$_POST['dir']),true,true);
            $_POST['qr']=qr($url,$_POST['dir']);
            if(M('patient') ->where('id='.$_GET['id'])->save($_POST)){
                if(rename($_GET['root_dir'].$_GET['dir'],$_GET['root_dir'].$_POST['dir'])){
                    $this ->success('更换成功');
                }else{
                    $this ->error('数据更新成功,文件名更新失败,请手动更换或者重新更换');
                }

            }else{
                $this ->error('删除失败');
            }
    }


    /**
     *  编辑
     */
    public function edit() {
        import("Tree");
        $tree = new \Tree();
        $id = intval(I("get.id"));
        $rs = $this->menu_model->where(array("id" => $id))->find();
        $result = $this->menu_model->order(array("listorder" => "ASC"))->select();
        foreach ($result as $r) {
        	$r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
        	$array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("data", $rs);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }

}

