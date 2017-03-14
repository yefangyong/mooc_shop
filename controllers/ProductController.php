<?php
namespace app\controllers;
use yii\web\controller;

class ProductController extends controller {

    public function actionIndex() {
        $this->layout='layout2';
        return $this->render('index');
    }

    public function actionDetail() {
        $this->layout='layout2';
        return $this->render('detail');
    }
}