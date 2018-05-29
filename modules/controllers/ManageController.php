<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 21:59
 */

namespace app\modules\controllers;

use app\modules\models\admin;
use yii\web\Controller;

class ManageController extends Controller
{
    public function actionMailChangePass() {
        $time = \Yii::$app->request->get('timestamp');
        $adminuser = \Yii::$app->request->get('adminuser');
        $token = \Yii::$app->request->get('token');
        $model = new Admin;
        $myToken = $model->createToken($adminuser,$time);
        if($token != $myToken) {
            $this->redirect(['public/login']);
            \Yii::$app->end();
        }
        if(time() - $time > 300) {
            $this->redirect(['public/login']);
            \Yii::$app->end();
        }
        if(\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            //修改密码
            if($model->changePass($post)) {
                $this->render(['public/login']);
                \Yii::$app->end();
            }
        }
        return $this->render('mailChangePass',['model'=>$model]);
    }
}