<?php
namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

class admin extends ActiveRecord {
    public $rememberMe = '记住我';

    public static function tableName() {
        return "{{%admin}}";
    }

    public function login($data) {
        $this->scenario = 'login';
        $session = Yii::$app->session;
       if($session['admin']['isLogin']) {
           return true;
       }
        if($this->load($data) && $this->validate()) {
            $lifetime = $this->rememberMe ? 24*3600 :0;
            session_set_cookie_params($lifetime);
            $session['admin'] = [
                'adminuser'=>$this->adminuser,
                'isLogin'=>'1',
            ];
            $this->updateAll([
                'logintime'=>time(),
                'loginip'=>ip2long(Yii::$app->request->userIP)],
                'adminuser = :user',[':user'=>$this->adminuser]
            );
            return (bool)$session['admin']['isLogin'];
        }
        return false;
    }


    public function rules()
    {
        return [
            ['adminuser','required','message'=>'管理员的账号不得为空','on'=>['login','seekpass']],
            ['adminpass','required','message'=>'管理员的密码不得为空','on'=>'login'],
            ['rememberMe','boolean','on'=>'login'],
            ['adminpass','validatePass','on'=>'login'],
            ['adminemail','required','message'=>'管理员电子邮箱不得为空','on'=>'seekpass'],
            ['adminemail','email','message'=>'电子邮箱格式不正确','on'=>'seekpass'],
            ['adminemail','validateEmail','on'=>'seekpass'],
        ];
    }

    public function validateEmail() {
        if(!$this->hasErrors()) {
            $data = self::find()->where('adminuser=:user and adminemail=:email',[':user'=>$this->adminuser,':email'=>$this->adminemail])->one();
            if(is_null($data)) {
                $this->addError("adminemail",'用户名和邮箱不匹配');
            }
        }
    }

    public function validatePass() {
        if(!$this->hasErrors()) {
            $data = self::find()->where('adminuser = :user and adminpass = :pass',[':user'=>$this->adminuser,':pass'=>md5($this->adminpass)])->one();
            if(is_null($data)) {
                $this->addError("adminpass",'用户名或者密码错误');
            }
        }
    }

    public function seekPass($data) {
        $this->scenario='seekpass';
        if($this->load($data) && $this->validate()) {
            //做点有意义的事情
        }
        return false;
    }
}