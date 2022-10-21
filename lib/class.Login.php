<?php

class Login extends Base
{
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
        if (empty($this->post['password'])){
            return self::returnActionResult([],false,"Please input the password");
        }
        foreach ($this->users as $user){
            if ($user['UserName']==$inputUserName && $user['Password']==$inputPassword){
                return self::returnActionResult(
                    [
                        'Token'=>md5($inputUserName."_".$inputPassword)
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
}