<?php
namespace app\controllers;
use yii\web\controller;

class CartController extends controller {
    public function actionIndex() {
        $this->layout='layout1';
        return $this->render('index');
    }
}