<?php

class Login extends Base
{
    protected $loginPath='';
    public function __construct($get = [], $post = '')
    {
        parent::__construct($get, $post);
        $this->loginPath=INDEX_FILE."LoginUsers".DIRECTORY_SEPARATOR;
    }

    private $users=[
        [
            'UserName'=>'admin',
            'Password'=>'admin'
        ]
    ];
    protected $authCheck = false;
    public function PasswordCheck(){
        $this->post=json_decode($this->post,1);
        $inputUserName=$this->post['UserName'];
        $inputPassword=$this->post['Password'];
        if (empty($inputPassword) || empty($inputUserName)){
            return self::returnActionResult([],false,"Please set the data.");
        }
        foreach ($this->users as $user){
            if ($user['UserName']==$inputUserName && $user['Password']==$inputPassword){
                $token=md5($inputUserName."_".$inputPassword."_".date("Y-m-d"));
                $this->loginSuccess($token);
                return self::returnActionResult(
                    [
                        'Token'=>$token
                    ]
                );
            }
        }
        return self::returnActionResult(
            [],
            false,
            'Username or Password error'
        );
    }

    public function isLogin($token){
        return file_exists($this->loginPath.$token);
    }

    public function loginSuccess($token){

        if (!is_dir($this->loginPath)){
            mkdir($this->loginPath);
        }
        file_put_contents($this->loginPath.$token,date("Y-m-d H:i:s").PHP_EOL,FILE_APPEND);
    }
}