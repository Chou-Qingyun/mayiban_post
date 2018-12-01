<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use \think\Request;
use \think\File;
use \think\Db;
use \think\Validate;
class MobileController extends HomeBaseController 
{
	public function index() {
		return $this->fetch(':mobile');
	}
	
	public function home() {
	    return $this->fetch(':home');
   	}
	// 提交数据
	public function addData() {
		$imgErrorArr = [];
		$postData = Request::instance()->post();
		// 表单后台数据再次验证
		$video_path = Request::instance()->post('video_path');
		$rule = [
			'name' => 'require|max: 25',
			'phone' => 'require|regex:\d{11}|unique:user_resource',
			'mailbox' => 'require|email',
			'city' => 'require'
		];

		$msg = [
			'name.require' => '请填写姓名',
			'name.max' => '姓名最多不能超过25个字符',
			'phone.require' => '请填写手机号',
			'phone.regex' => '请使用正确的手机号',
			'phone.unique' => '该手机号已被使用，请更换其他手机号',
			'mailbox.require' => '请填写邮箱',
			'mailbox.email' => '请填写正确的邮箱',
			'city.require' => '请填写城市名称'
		];

		if ($video_path) {
			$rule['video_path'] = 'url';
			$msg['video_path.url'] = '请填写正确的视频链接地址';
		}

		$validate = new Validate($rule, $msg);

		if (!$validate->check($postData)) {
			$resopnseMsg['state'] = false;
			$resopnseMsg['errormsg'] = $validate->getError();
			return json($resopnseMsg);
		}

		$phoneNum = Request::instance()->post('phone');
		/*
		// 上传图片
		$imgFile = Request::instance()->file('file');
		foreach($imgFile as $file) {
		@mkdir(ROOT_PATH.'/public/upload/'.date('Y-m-d', time()).'/'.$phoneNum, 0777,true);
		@chmod(ROOT_PATH.'/public/upload/'.date('Y-m-d', time()).'/'.$phoneNum, 0777);
			// 移动到对应手机号的文件夹下
			$resInfo = $this->uploadFile($file, $phoneNum);
			if ($resInfo['state']) {
				$imgFileArr[] = $resInfo['upload_path'];
				@chmod(ROOT_PATH.'/public/upload/'.date('Y-m-d', time()), 0777);
			} else {
				$imgErrorArr = $resInfo;
				break;
			}
		}
		if (!empty($imgErrorArr)) {
			return json($imgErrorArr);
		}*/
		// 上传视频
		$videoFile = Request::instance()->file('mv');
		if ($videoFile) {
			$mvRes = $this->uploadFile($videoFile, $phoneNum);
			if ($mvRes['state']) {
				$mvFilePath = $mvRes['upload_path'];
			} else {
				return json($mvRes);
			}
			$postData['video'] = $mvFilePath;
			//如果有传视频，则不存链接路径
			$postData['video_path'] = '';
		}

		//存入表单
		$postData['create_time'] = time();
		$res = Db::name('user_resource')->insert($postData);
		if ($res) {
			return json(array('state'=>true,'succ'=>'提交成功'));
		} else {
			return json(array('state'=>false, 'errormsg'=> '提交失败，请稍候再试'));
		}
	}

	// 验证手机号是否存在
	public function existsPhone() {
		$phone = Request::instance()->post('phone');
		$result = Db::name('user_resource')->where('phone', $phone)->find();
		if ($result) {
			return json(false);
		} else {
			return json(true);
		}

	}


	public function uploadImg() {
	    $file = Request::instance()->file('file');
	    $phone = Request::instance()->post('phone');
	    $uploadRes = $this->uploadFile($file, $phone);
	    if ($uploadRes['state']) {
            return json(['url' => $uploadRes['upload_path']]);
        }else {
	        return json(['url'=>false]);
        }


    }

	/*上传方法*/	
	protected function uploadFile($file, $path) {
		// 移动到$path文件夹下
		$info = $file->move(ROOT_PATH . 'public' . DS . 'upload' . DS . $path);
		if ($info) {
			$data['state'] = true;
			$data['upload_path'] = $path . DS . $info->getSaveName();
		} else {
			// 返回上传失败的消息
			$data['state'] = false;
			$data['errormsg'] = $file->getError();
		}
		return $data;
	}

	public function pushData() {
	    $data = Request::instance()->post();
	    $postData['city'] = $data['city'];
	    $postData['name'] = $data['name'];
	    $postData['phone'] = $data['phone'];
	    $postData['mailbox'] = $data['mailbox'];
	    $postData['pictures'] = $data['img_arr'];
	    $postData['video'] = $data['mv_path'];
	    $postData['video_path'] = $data['video_path'];

        $rule = [
            'name' => 'require|max: 25',
            'city' => 'require',
            'phone' => 'require|regex:\d{11}|unique:user_resource',
            'mailbox' => 'require|email'
        ];

		$msg = [
            'name.require' => '请填写姓名',
            'city.require' => '请填写城市名称',
            'name.max' => '姓名最多不能超过25个字符',
            'phone.require' => '请填写手机号',
            'phone.regex' => '请使用正确的手机号',
            'phone.unique' => '该手机号已被使用，请更换其他手机号',
            'mailbox.require' => '请填写邮箱',
            'mailbox.email' => '请填写正确的邮箱'
        ];

		if ($postData['video_path']) {
            $rule['video_path'] = 'url';
            $msg['video_path.url'] = '请填写正确的视频链接地址';
        }

		$validate = new Validate($rule, $msg);

		if (!$validate->check($postData)) {
            $resopnseMsg['state'] = false;
            $resopnseMsg['errormsg'] = $validate->getError();
            return json($resopnseMsg);
        }
        $postData['create_time'] = time();
        $postData['post_from'] = 1;
        $res = Db::name('user_resource')->insert($postData);
        if ($res) {
            return json(array('state'=>true,'succ'=>'提交成功'));
        } else {
            return json(array('state'=>false, 'errormsg'=> '提交失败，请稍候再试'));
        }

    }

	




}

 ?>
