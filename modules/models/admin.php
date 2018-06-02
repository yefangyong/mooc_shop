<?php
namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

class admin extends ActiveRecord {
    public $rememberMe = '记住我';
    public $repass;
    public static function tableName() {
        return "{{%admin}}";
    }

    public function attributeLabels()
    {
        return array(
            'adminuser'=>'管理员账号',
            'adminemail'=>'管理员邮箱',
            'adminpass'=>'管理员密码',
            'repass'=>'再次输入密码'
        );
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
            ['adminuser', 'required', 'message' => '管理员账号不能为空', 'on' => ['login', 'seekpass', 'changepass', 'reg', 'changeemail']],
            ['adminpass', 'required', 'message' => '管理员密码不能为空', 'on' => ['login', 'changepass', 'reg', 'changeemail']],
            ['rememberMe', 'boolean', 'on' => 'login'],
            ['adminpass', 'validatePass', 'on' => ['login', 'changeemail']],
            ['adminemail', 'required', 'message' => '电子邮箱不能为空', 'on' => ['seekpass', 'reg', 'changeemail']],
            ['adminemail', 'email', 'message' => '电子邮箱格式不正确', 'on' => ['seekpass', 'reg', 'changeemail']],
            ['adminemail', 'unique', 'message' => '电子邮箱已被注册', 'on' => ['reg', 'changeemail']],
            ['adminuser', 'unique', 'message' => '管理员已被注册', 'on' => 'reg'],
            ['adminemail', 'validateEmail', 'on' => 'seekpass'],
            ['repass', 'required', 'message' => '确认密码不能为空', 'on' => ['changepass', 'reg']],
            ['repass', 'compare', 'compareAttribute' => 'adminpass', 'message' => '两次密码输入不一致', 'on' => ['changepass', 'reg']],
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

    /**
     * 验证密码
     */
    public function validatePass() {
        if(!$this->hasErrors()) {
            $data = self::find()->where('adminuser = :user and adminpass = :pass',[':user'=>$this->adminuser,':pass'=>md5($this->adminpass)])->one();
            if(is_null($data)) {
                $this->addError("adminpass",'用户名或者密码错误');
            }
        }
    }

    /**
     * @param $data
     * @return bool
     * 找回密码and发送邮件
     */
    public function seekPass($data) {
        $this->scenario='seekpass';
        if($this->load($data) && $this->validate()) {
            //做点有意义的事情
            $time = time();
            $token = $this->createToken($data['admin']['adminuser'],$time);
            $mailer = Yii::$app->mailer->compose('seekpass',['adminuser'=>$data['admin']['adminuser'],'time'=>time(),'token'=>$token]);
            $mailer->setFrom("13053112897@163.com");
            $mailer->setTo($data['admin']['adminemail']);
            $mailer->setSubject("慕课商城-找回密码");
            if($mailer->send()){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $adminuser
     * @param $time
     * @return string
     * 创建token
     */
    protected function createToken($adminuser,$time) {
        return md5(md5($adminuser).base64_encode(Yii::$app->request->userIP).md5($time));
    }

    /**
     * @param $data
     * @return bool
     * 修改密码
     */
    public function changePass($data)
    {
        $this->scenario = "changepass";
        if ($this->load($data) && $this->validate()) {
            return (bool)$this->updateAll(['adminpass' => md5($this->adminpass)], 'adminuser = :user', [':user' => $this->adminuser]);
        }
        return false;
    }

    /**
     * @param $data
     * @return bool
     * 添加管理员
     */
    public function reg($data) {
        $this->scenario = 'reg';
        if($this->load($data) && $this->validate()) {
            $this->adminpass = md5($this->adminpass);
            if($this->save(false)) {
                return true;
            }else {
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param $data
     * @return bool
     * 修改邮箱
     */
    public function changeEmail($data) {
        $this->scenario = 'changeemail';
        if($this->load($data) && $this->validate()) {
            $admin = self::find()->where('adminuser=:user',[':user'=>Yii::$app->session['admin']['adminuser']])->one();
            if($admin['adminpass'] != md5($this->adminpass)) {
                return false;
            }
            $res = $this->update(array('adminemail'=>$this->adminemail,'adminuser=:user',[':user'=>$this->adminuser]));
            if($res) {
                return true;
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

}