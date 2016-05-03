<?php
namespace app\common\model;
trait UserTrait 
{
	private function create_token($id)
	{
		 return mt_rand()."_".$id;
	}
	public function login($username,$password)
	{
		$r=$this->db->where(['username'=>$username,'password'=>$password])->field("password",true)->find();
		if(!$r)
		{
			return NULL;	
		}
		$token=$this->create_token($r['id']);
		$this->save(['token'=>$token],['id'=>$r['id']]);
		$this->data['token']=$token;
		$this->data=$r;
		return $token;
	}
	public function check_token($token)
	{
		$r=$this->db->where(['token'=>$token])->field("password",true)->find();
		if(!$r)
		{
			return NULL;	
		}
		else
		{
			$this->data=$r;
			return $r;
		}	
	}
}
