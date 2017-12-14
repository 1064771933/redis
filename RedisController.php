<?php
/**
 * Created by PhpStorm.
 * User: aaaa
 * Date: 2017/12/12
 * Time: 16:20
 */
namespace app\controllers;
use Codeception\Module\Redis;
use Yii;
use app\models\Member_group;
use app\models\News;
use app\models\Users;
use yii\web\Controller;
use app\models\Member;
use app\models\Member_detail;
use app\models\Category_priv;
use yii\helpers\Json;

class RedisController extends Controller
{
    public $enableCsrfValidation=false;
    public function actionRedis(){
        $this->layout=false;
        $redis=Yii::$app->redis;
//        $pagesize=8;
//        $count=$redis->get('userid');//获取总条数值
//        $page=Yii::$app->request->get('page')?Yii::$app->request->get('page'):1;//获取页数值 如果没有默认1
//        $ids=$redis->lrange('uid',$pagesize*($page-1),$pagesize*$page-1);//获取区间的数据
//        //遍历区间数据
//        foreach ($ids as $info){
//            $ulist[]=$redis->hgetall('user:'.$info);
//        }
//        return $this->render('redis',['ulist'=>$ulist,'page'=>$page]);//映射页面
        $userlist=Users::find()->all(); //查询数据库
        //存入redis
        foreach ($userlist as $key=>$v){
            $redis->incr('userid');
            $redis->hmset('user:'.$key,
                                'id',$v['id'],
                                'username',$v['username'],
                                'sex',$v['sex'],
                                'idcate',$v['idcate'],
                                'dorm_id',$v['dorm_id'],
                                'iclass',$v['iclass'],
                                'adress',$v['adress'],
                                'nation',$v['nation'],
                                'stutel',$v['stutel'],
                                'birthday',$v['birthday']
                );
            $redis->rpush('uid',$key);
        }
//        echo "<pre/>";
//        print_r($userlist);
        //测试
//        $redis->set('username','ergou');
//       echo $redis->get('username');

    }
    public function actionSelect()//查询分页
    {
       $this->layout=false;
       $redis=Yii::$app->redis;
       $pagesize=8;
       $count=$redis->get('userid');//获取总条数值
       $page=Yii::$app->request->get('page')?Yii::$app->request->get('page'):1;//获取页数值 如果没有默认1
       $ids=$redis->lrange('uid',$pagesize*($page-1),$pagesize*$page-1);//获取区间的数据
       //遍历区间数
       end($ids);
       $sum=key($ids);
       foreach ($ids as $info){

           $ulist[]=$redis->hgetall('user:'.$info);
           
       }
       for ($i=0;$i<=$sum;$i++){
            
          $ulist[$i]['uid']=$ids[$i];
           
       }

       // echo "<pre>";
       // print_r($ulist);exit;
       return $this->render('select',['ulist'=>$ulist,'page'=>$page]);//映射页面
    }
     public function actionAdd()//增
    {
        $this->layout=false;
        
        return $this->render('add');
    }
    public function actionDo_add()//增
    {
        $data=Yii::$app->request->post();
        $this->layout=false;
        $redis=Yii::$app->redis;
        $count=$redis->get('userid')+1;
        echo $count;
        //print_r($data);exit;
        $redis->incr('userid');
        $add=$redis->hmset('user:'.$count,'id',$count,
                                'username',$data['username'],
                                'sex',$data['sex'],
                                'idcate',$data['idcate'],
                                'dorm_id',$data['dorm_id'],
                                //'iclass',$data['iclass'],
                                'adress',$data['adress'],
                                'nation',$data['nation']
                                //'stutel',$data['stutel'],
                                //'birthday',$data['birthday']
                                );
        $redis->rpush('uid',$count);
        if($add){
            echo 'succ';
        }else{
            echo "fail";
        }
        return $this->render('add');
    }
     public function actionDo_up()//改
    {
        $data=Yii::$app->request->post();
        $this->layout=false;
        $redis=Yii::$app->redis;
        
        
        //print_r($data);exit;
        
        $add=$redis->hmset('user:'.$data['id'],'id',$data['id'],
                                'username',$data['username'],
                                'sex',$data['sex'],
                                'idcate',$data['idcate'],
                                'dorm_id',$data['dorm_id'],
                                //'iclass',$data['iclass'],
                                'adress',$data['adress'],
                                'nation',$data['nation']
                                //'stutel',$data['stutel'],
                                //'birthday',$data['birthday']
                                );
        if($add){
            echo 'succ';
        }else{
            echo "fail";
        }
        // return $this->render('up');
    }
    public function actionUp()//改
    {
        $redis=Yii::$app->redis;
        $this->layout=false;
        $id=Yii::$app->request->get('id');
        echo $id;
        $info=$redis->hgetall('user:'.$id);
        // echo "<pre>";
        // print_r($info);
        // exit;
        return $this->render('up',['info'=>$info]);
    }
     public function actionDeluser()//删
    {
        $this->layout=false;
        $id=Yii::$app->request->get('uid');
        $redis=Yii::$app->redis;
        $del=$redis->del('user:'.$id);
        $redis->lrem('uid',1,$id);
        $redis->decr('userid');
         if($del){
            echo 'succ';
        }else{
            echo "fail";
        }
    }
    public function actionTest()
    {
        $this->layout=false;
        Yii::$app->redis->set('test','hello yii2-reids');  //设置redis缓存
        echo Yii::$app->redis->get('test');   //读取redis缓存
        exit;
        return $this->render('test');
    }
}