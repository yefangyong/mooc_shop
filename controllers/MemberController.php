<?php
namespace app\controllers;
use yii\web\controller;

class MemberController extends controller {
    public function actionAuth() {
        $this->layout=false;
        $this->render('auth');
    }
}