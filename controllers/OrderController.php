<?php
namespace app\controllers;
use yii\web\controller;

class OrderController extends controller {
    public function actionCheck() {
        $this->layout='layout1';
        return $this->render('check');
    }

    public function actionIndex() {
        $this->layout='layout2';
        return $this->render('index');
    }
}