<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use \think\Request;
use PHPExcel_IOFactory;
use PHPExcel;           //USE phpoffice/phpspreadsheet


class WetchatResourceController extends AdminBaseController {

    public function index() {
        $where   = [];
        $where['post_from'] = 4;
        $request = input('request.');

        if (!empty($request['uid'])) { // 用户ID
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {  // 关键字 用户名/邮箱/手机
            $keyword = $request['keyword'];

            $keywordComplex['name|mailbox|phone|college']    = ['like', "%$keyword%"];

        }
        // 用户资源表
        $usersQuery = Db::name('user_resource');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $arr = [];
        foreach($list as $value) {
            $item['id'] = $value['id'];
            $item['name'] = $value['name'];
            $item['phone'] = $value['phone'];
            $item['college'] = $value['college'];
            $item['city'] = $value['city'];
            $item['mailbox'] = $value ['mailbox'];
            $item['pictures'] = explode('|', substr($value['pictures'], 0, -1));
            $item['video'] = $value['video'];
            $item['create_time'] = $value['create_time'];
            $item['video_path'] = $value['video_path'];
            $arr[] = $item;
        }

        // 获取分页显示
        $page = $list->render();
        $this->assign('from', $where['post_from']);
        $this->assign('list', $arr);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch('../admin/resource:index2');
    }
    public function index3() {
        $where   = [];
        $where['post_from'] = 5;
        $request = input('request.');

        if (!empty($request['uid'])) { // 用户ID
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {  // 关键字 用户名/邮箱/手机
            $keyword = $request['keyword'];

            $keywordComplex['name|mailbox|phone|college']    = ['like', "%$keyword%"];

        }
        // 用户资源表
        $usersQuery = Db::name('user_resource');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $arr = [];
        foreach($list as $value) {
            $item['id'] = $value['id'];
            $item['name'] = $value['name'];
            $item['phone'] = $value['phone'];
            $item['college'] = $value['college'];
            $item['city'] = $value['city'];
            $item['mailbox'] = $value ['mailbox'];
            $item['pictures'] = explode('|', substr($value['pictures'], 0, -1));
            $item['video'] = $value['video'];
            $item['create_time'] = $value['create_time'];
            $item['video_path'] = $value['video_path'];
            $arr[] = $item;
        }

        // 获取分页显示
        $page = $list->render();
        $this->assign('from', $where['post_from']);
        $this->assign('list', $arr);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch('../admin/resource:index2');
    }

    public function indexList($from = 3) {
        $where   = [];
        $where['post_from'] = $from;
        $request = input('request.');

        if (!empty($request['uid'])) { // 用户ID
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {  // 关键字 用户名/邮箱/手机
            $keyword = $request['keyword'];

            $keywordComplex['name|mailbox|phone|college']    = ['like', "%$keyword%"];

        }
        // 用户资源表
        $usersQuery = Db::name('user_resource');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $arr = [];
        foreach($list as $value) {
            $item['id'] = $value['id'];
            $item['name'] = $value['name'];
            $item['phone'] = $value['phone'];
            $item['college'] = $value['college'];
            $item['city'] = $value['city'];
            $item['mailbox'] = $value ['mailbox'];
            $item['pictures'] = explode('|', substr($value['pictures'], 0, -1));
            $item['video'] = $value['video'];
            $item['create_time'] = $value['create_time'];
            $item['video_path'] = $value['video_path'];
            $arr[] = $item;
        }

        // 获取分页显示
        $page = $list->render();
        $this->assign('from', $where['post_from']);
        $this->assign('list', $arr);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch('../admin/resource:index2');

    }

    // 导出选手资料
    public function exportExcel() {
        $postData = Request::instance()->post('idStr');
        $from = Request::instance()->post('from');
        $field = 'id,name,phone,mailbox,city,college,pictures,video,create_time';
        if ($postData === 'all') {
            $where['post_from'] = $from ;
            $result = Db::name('user_resource')->where($where)->field($field)->order('id desc')->select();
        } else {
            $condition = explode(',', $postData);
            array_pop($condition);
            $result = Db::name('user_resource')->whereIn('id', $condition)->field($field)->order('id desc')->select();
        }
        $PHPExcel = new PHPExcel();
        $path = ROOT_PATH . '/public/upload/excel/';
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('爱奇艺超级主播全国网络主播选秀大赛');
        $PHPSheet->getColumnDimension('A')->setAutoSize(true);
        $PHPSheet->getColumnDimension('B')->setWidth(20);
        $PHPSheet->getColumnDimension('C')->setAutoSize(true);
        $PHPSheet->getColumnDimension('D')->setWidth(15);
        $PHPSheet->getColumnDimension('F')->setWidth(20);
        $PHPSheet->getColumnDimension('H')->setWidth(20);
        $PHPSheet->getColumnDimension('I')->setAutoSize(true);
        $PHPSheet->getColumnDimension('J')->setAutoSize(true);

        $PHPSheet->setCellValue('A1','ID')
            ->setCellValue('B1', '姓名')
            ->setCellValue('C1', '手机号')
            ->setCellValue('D1', '邮箱')
            ->setCellValue('E1', '所在城市')
            ->setCellValue('F1', '推荐/所在院')
            ->setCellValue('G1', '图片1链接')
            ->setCellValue('H1', '图片2链接')
            ->setCellValue('I1', '图片3链接')
            ->setCellValue('J1', '视频')
            ->setCellValue('K1', '上传日期');

        foreach($result as $key => $item) {
            $j = 'A';
            $key += 2;
            foreach($item as $k =>  $value) {
                if (empty($item[$k])) {
                    $PHPSheet->setCellValue($j.$key, '');
                } elseif ($k === 'pictures') {
                    $picArr = explode('|', $value);
                    array_pop($picArr);
                    foreach($picArr as $picKey => $picUrl) {
                        $url = 'http://cjzb.mayiban.cn/upload/' . $picUrl;
                        $picStr = '图片' . intval($picKey + 1) ;
                        $PHPSheet->setCellValue($j.$key, $picStr);
                        $PHPSheet->getCell($j.$key)->getHyperlink()->setUrl($url);
                        $PHPSheet->getStyle($j.$key)->getFont(14)->setBold(true)->setUnderline(true)
                            ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_BLUE));
                        $j++;
                    }
                    continue;
                } elseif ($k === 'video'  || $k === 'video_path') {
                    $video_url = 'http://cjzb.mayiban.cn/upload/' . $item[$k];
                    $PHPSheet->setCellValue($j.$key, '查看视频');
                    if ($k === 'video') {
                        $PHPSheet->getCell($j.$key)->getHyperlink()->setUrl($video_url);
                    } else {
                        $PHPSheet->getCell($j.$key)->getHyperlink()->setUrl($value);
                    }
                    $PHPSheet->getStyle($j.$key)->getFont(14)->setBold(true)->setUnderline(true)
                        ->setColor(new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_BLUE));

                } elseif ( $k === 'create_time') {
                    $date = date('Y-m-d H:i:s', $value);
                    $PHPSheet->setCellValue($j.$key, $date);
                } else {
                    $PHPSheet->setCellValue($j.$key, $value);
                }
                $j++;
            }
        }
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $time = time();
        $objWriter->save($path . $time . "_superAnthor.xlsx");
        return json(array('url' => 'http://cjzb.mayiban.cn/upload/excel/'. $time .'_superAnthor.xlsx'));
    }

    // 删除选手资料
    public function deleteAct()
    {
        $postData = Request::instance()->post('idStr');
        $condition = explode(',', $postData);
        array_pop($condition);
        $result = Db::name('user_resource')->whereIn('id', $condition)->order('id desc')->select();
        foreach ($result as $item) {
            //删除图片
            $imgArr = explode('|', $item['pictures']);
            if (!empty($imgArr)) {
                foreach($imgArr as $img) {
                    $img_path = ROOT_PATH . '/public/upload/'. $img . "\n\r";
                    if(file_exists($img_path)) {
                        chmod($img_path, 0777);
                        unlink($img_path);
                    }
                }
            }
            //删除本地视频
            if($item['video']) {
                $video_src = ROOT_PATH . '/public/upload/' . $item['video'];
                if (file_exists($video_src)) {
                    chmod($video_src, 0777);
                    unlink($video_src);
                }
            }
        }
        //删除数据库记录
        $del_res = Db::name('user_resource')->delete($condition);
        if ($del_res) {
            return json(array('state'=>true));
        } else {
            return json(array('state'=>false, 'errormsg' => '删除失败，请稍后再试！'));
        }

    }



}

