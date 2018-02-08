<?php
namespace console\controllers;
use backend\models\Book;
use backend\models\Recharge;
use libs\PostRequest;
use yii\console\Controller;

class TaskController extends Controller{
    //手动清理超时未支付订单(24小时)
    public function actionClean(){
        //设置脚本执行时间(不终止)
        //set_time_limit(0);
        //当前时间 - 创建时间 > 24小时   ---> 创建时间 <  当前时间 - 24小时
        //超时未支付订单
        //sql: update order set status=0 where status = 1 and create_time < time()-24*3600
        //while (true){
            //Recharge::deleteAll('status=1 and create_time < '.(time()-60));
            Recharge::deleteAll('create_time < :create_time AND status = :status', [':create_time' =>(time()-180) , ':status' => '1']);
            //每隔一秒执行一次
            //sleep(1);
            echo '清理完成'.date('Y-m-d H:i:s')."\n";
       // }

    }

    //更新凯兴版权书数据
    public function actionUpdateCopyright(){
        $postUrl = 'http://partner.chuangbie.com/partner/booklist';
        $curlPost =['partner_id'=>2130,'partner_sign'=>'b42c36ddd1a5cc2c6895744143f77b7b','page_size'=>100];
        $post=new PostRequest();
        $data=$post->request_post($postUrl,$curlPost);
        $datas=json_decode($data,true);
        foreach ($datas['content']['data'] as $data){
            Book::updateAll(
                ['size'=>$data['word_count']*2,'is_end'=>$data['status'],'last_update_chapter_id'=>$data['last_update_chapter_id'],'last_update_chapter_name'=>$data['last_update_chapter_name'],'update_time'=>time()],
                ['name'=>$data['book_name']]);

        }

        echo '批量更新成功'.date('Y-m-d H:i:s')."\n";

    }
}