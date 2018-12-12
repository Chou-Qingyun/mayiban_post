<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use \think\Request;
use PHPExcel_IOFactory;
use PHPExcel;           //USE phpoffice/phpspreadsheet


class MatchResourceController extends AdminBaseController {

    public function indexList() {
        $where   = [];
        $where['post_from'] = 2;
        $request = input('request.');

        if (!empty($request['uid'])) { // 用户ID
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {  // 关键字 用户名/邮箱/手机
            $keyword = $request['keyword'];

            $keywordComplex['name|mailbox|phone']    = ['like', "%$keyword%"];

        }
        // 用户资源表
        $usersQuery = Db::name('user_resource');

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        $arr = [];
        foreach($list as $value) {
            $item['id'] = $value['id'];
            $item['name'] = $value['name'];
            $item['phone'] = $value['phone'];
            $item['city'] = $value['city'];
            $item['weixin'] = $value['weixin'];
            $item['game_name'] = $value['game_name'];
            $item['pictures'] = explode('|', substr($value['pictures'], 0, -1));
            $item['create_time'] = $value['create_time'];
            $arr[] = $item;
        }

        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $arr);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch('../admin/resource:match');

    }

    // 导出选手资料
    public function exportExcel() {
        $postData = Request::instance()->post('idStr');

        if ($postData === 'all') {
            $result = Db::name('user_resource')->order('id desc')->select();
        } else {
            $condition = explode(',', $postData);
            array_pop($condition);
            $result = Db::name('user_resource')->whereIn('id', $condition)->order('id desc')->select();
        }

        $PHPExcel = new PHPExcel();
        $path = ROOT_PATH . '/public/upload/excel/';
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('爱奇艺超级主播全国网络主播选秀大赛');
        $PHPSheet->getColumnDimension('A')->setWidth(20);
        $PHPSheet->getColumnDimension('B')->setAutoSize(true);
        $PHPSheet->getColumnDimension('C')->setAutoSize(true);
        $PHPSheet->getColumnDimension('H')->setWidth(20);
        $PHPSheet->getColumnDimension('I')->setAutoSize(true);

        $PHPSheet->setCellValue('A1','姓名')
            ->setCellValue('B1', '手机号')
            ->setCellValue('C1', '邮箱')
            ->setCellValue('D1', '所在城市')
            ->setCellValue('E1', '图片1链接')
            ->setCellValue('F1', '图片2链接')
            ->setCellValue('G1', '图片3链接')
            ->setCellValue('H1', '视频链接')
            ->setCellValue('I1', '上传日期');
        foreach($result as $key => $item) {
            $j = 'A';
            $key += 2;
            foreach($item as $k =>  $value) {
                if ($k === 'id' || empty($item[$k])) {
                    continue;
                } elseif ($k === 'pictures') {
                    $picArr = explode('|', $value);
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
                } elseif ($k === 'video'  || $k === 'video_path' ) {
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
        $objWriter->save($path . "superAnthor.xlsx");
        return json(array('url' => 'http://cjzb.mayiban.cn/upload/excel/superAnthor.xlsx'));
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

