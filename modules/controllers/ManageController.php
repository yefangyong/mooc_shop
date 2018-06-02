<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 21:59
 */

namespace app\modules\controllers;

use app\modules\models\admin;
use yii\data\Pagination;
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

    /**
     * @return string
     * 管理员列表
     */
    public function actionManagers(){
        $this->layout = "layout1";
        $model = Admin::find();
        $pageSize = \Yii::$app->params['pageSize']['manager'];
        $page = new Pagination(['totalCount'=>$model->count(),'pageSize'=>$pageSize]);
        $managers = $model->offset($page->offset)->limit($page->limit)->all();
        return $this->render('managers',['managers'=>$managers,'page'=>$page]);
    }

    /**
     * @return string
     * 添加管理员
     */
    public function actionReg() {
        $model = new Admin;
        if(\Yii::$app->request->isPost) {
            //添加
            $post = \Yii::$app->request->post();
            if($model->reg($post)){
                \Yii::$app->session->setFlash('info','添加成功');
            }else {
                \Yii::$app->session->setFlash('info','添加失败');
            }
        }
        $this->layout = 'layout1';
        return $this->render('reg.php',['model'=>$model]);
    }

    /***
     *删除用户
     */
    public function actionDel() {
        $adminid = \Yii::$app->request->get('adminid');
        if(empty($adminid)) {
            $this->redirect(['manage/managers']);
        }
        $model = new Admin;
        if($model->deleteAll('adminid=:id',[':id'=>$adminid])){
            \Yii::$app->session->setFlash('info','删除成功');
            $this->redirect(['manage/managers']);
        }else {
            \Yii::$app->session->setFlash('info','删除失败');
        }
    }

    /**
     * @return string
     * 修改邮箱
     */
    public function actionChangeemail() {
        $this->layout = 'layout1';
        $model = Admin::find()->where('adminuser=:user',[':user'=>\Yii::$app->session['admin']['adminuser']])->one();
        if(\Yii::$app->request->isPost) {
            //修改
            $data = \Yii::$app->request->post();
            if($model->changeEmail($data)) {
                \Yii::$app->session->setFlash('info','修改成功');
            }else {
                \Yii::$app->session->setFlash('info','修改失败');
            }
        }
            return $this->render('changeemail',['model'=>$model]);
    }

    public function actionChangepass() {
        $this->layout = 'layout1';
        $model = Admin::find()->where('adminuser=:user',[':user'=>\Yii::$app->session['admin']['adminuser']])->one();
        if(\Yii::$app->request->isPost) {
            //修改
            $data = \Yii::$app->request->post();
            if($model->changePass($data)) {
                \Yii::$app->session->setFlash('info','修改成功');
            }else {
                \Yii::$app->session->setFlash('info','修改失败');
            }
        }
        return $this->render('changepass',['model'=>$model]);
    }


}