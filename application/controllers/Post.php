<?php



class Post extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Post_model');
        $this->load->model('User_model');
        $this->load->model('Group_model');
        $this->load->model('Common_model');
        $this->load->helper('url_helper');
    }
    public function response($data,$ret=200,$msg=null){
        $response=array('ret'=>$ret,'data'=>$data,'msg'=>$msg);
        $this->output
            ->set_status_header($ret)
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
            ->_display();
        exit;
    }

    /**
     *
     */
    public function get_post_base(){
        $data = array(
            'post_id' =>$this->input->get('post_id'),
            'user_id' =>$this->input->get('user_id'),
        );
        $model = $this->Post_model;
        $common_model = $this->Common_model;
        $rs = $model->get_post_base($data['post_id'],$data['user_id']);
        $rs[0]['collect']=$common_model->judge_collect_post($data['post_id'],$data['user_id']);
        $group_id=$model->get_group_id($data['post_id']);
        $private_group = $common_model->judge_group_private($group_id);
        $rs[0]['edit_right']=0;
        $rs[0]['delete_right']=0;
        $rs[0]['sticky_right']=0;
        $rs[0]['lock_right']=0;
        $rs[0]['code'] = 1;
        $msg = '查看帖子成功';
        $re['group'] = $common_model->judge_group_exist($group_id);
        $re['post'] = $common_model->judge_post_exist($data['post_id']);
        if(!$re['post']){
            unset($rs);
            $rs[0]['code'] = 0;
            if($re['group']){
                $msg = "帖子已被删除，不可查看！";
            }else{
                $msg = "帖子所属星球已关闭，不可查看！";
            }
            $this->response($rs[0],200,$msg);
        }
        if($private_group){
            if($data['user_id'] !=null){
                $groupuser = $common_model->check_group($data['user_id'],$group_id);
                $groupcreator = $common_model->judge_group_creator($group_id,$data['user_id']);
                if(empty($groupcreator)){
                    if(empty($groupuser)){
                        unset($rs);
                        $rs[0]['code'] = 2;
                        $rs[0]['group_id'] = $group_id;
                        $msg = "未加入，不可查看私密帖子！";
                    }
                }
            }else{
                unset($rs);
                $rs[0]['code'] = 2;
                $rs[0]['group_id'] = $group_id;
                $msg = "未登录，不可查看私密帖子！";
            }
        }
        if ($data['user_id'] !=null){
            $creater= $common_model->judge_group_creator($group_id,$data['user_id']);
            $poster = $common_model->judge_post_creator($data['user_id'],$data['post_id']);
            $admin = $common_model->judge_admin($data['user_id']);
            if($poster)
            {
                $rs[0]['edit_right']=1;
                $rs[0]['delete_right']=1;
                $rs[0]['lock_right']=1;
            }
            if($creater){
                $rs[0]['delete_right']=1;
                $rs[0]['sticky_right']=1;
                $rs[0]['lock_right']=1;
            }
            if($admin){
                $rs[0]['delete_right']=1;
                $rs[0]['sticky_right']=1;
                $rs[0]['lock_right']=1;
            }
        }
        $this->response($rs[0],200,$msg);
    }
    public function get_post_reply(){
        $data = array(
            'post_id' =>$this->input->get('post_id'),
            'user_id' =>$this->input->get('user_id'),
            'pn'      =>$this->input->get('pn'),
        );
        $model = $this->Post_model;
        $common = $this->Common_model;
        $rs = $model->get_post_reply($data['post_id'],$data['pn'],$data['user_id']);
        $group_id = $model->get_post_information($data['post_id'])['group_base_id'];
        $sqlb = $common->judge_group_creator($group_id,$data['user_id']);
        $sqld = $common->judge_admin($data['user_id']);
        $sqle = $common->judge_post_creator($data['user_id'],$data['post_id']);
        foreach ($rs['reply'] as $key => $value) {
            $sqlc = $common->judge_post_reply_user($data['user_id'],$data['post_id'],$value['p_floor']);
            if ($sqlc||$sqlb||$sqld||$sqle) {
                $rs['reply']["$key"]['delete_right']=1;
            }else{
                $rs['reply']["$key"]['delete_right']=0;
            }
        }
        $rs = $common->delete_html_reply($rs);
        $this->response($rs,200,$msg='帖子回复显示成功');
    }
    public function post_reply(){
        $data = array(
            'post_base_id' =>$this->input->get('post_id'),
            'user_base_id' =>$this->input->get('user_id'),
            'text'  =>$this->input->get('p_text'),
            'reply_floor'=>$this->input->get('reply_floor')
        );
        $exist =$this->Common_model->judge_post_exist($data['post_base_id']);
        $lock=$this->Common_model->judge_post_lock($data['post_base_id']);
        if($exist&&!$lock) {
            $data = $this->Post_model->post_reply($data);
            $msg='回复成功';
            $rs = array(
                'code'=>1,
                'reply_page'=>$this->Common_model->get_post_reply_page($data['post_base_id'],$data['reply_floor']),
                'post_id'=>$data['post_base_id'],
                'user_id'=>$data['user_base_id'],
                'reply_id'=>$data['reply_id'],
                'p_floor'=>$data['floor'],
                'p_text'=>$data['text'],
                'create_time'=>$data['create_time'],
                'user_name'=>$this->User_model->get_user_information($data['user_base_id'])['nickname'],
                'reply_user_name'=>$this->User_model->get_user_information($data['reply_id'])['nickname'],
                'page'=>$this->Common_model->get_post_reply_page($data['post_base_id'],$data['floor']),
            );
        }else{
            $msg='帖子不存在或者被锁定';
            $rs['code'] = 0;
        }
        $this->response($rs,200,$msg);
    }
    public function edit_post(){
        $data = array(
            'post_base_id' =>$this->input->get('post_id'),
            'user_base_id' =>$this->input->get('user_id'),
            'text'  =>$this->input->get('p_text'),
            'title'=>$this->input->get('p_title')
        );
        $poster = $this->Common_model->judge_post_creator($data['user_base_id'],$data['post_base_id']);
        if($poster){
            $msg='编辑成功';
            $rs['code'] = 1;
            $rs['post_id']=$data['post_base_id'];
            $this->Post_model->edit_post($data);
        }else{
            $msg='您没有权限操作！';
            $rs['code'] = 0;
        }
        $this->response($rs,200,$msg);
    }









}