<?php

class Login extends Base
{
    protected $authCheck = false;
    public function PasswordCheck(){
        $this->post=json_decode($this->post,1);
        $password=date("dnH");
        if (empty($this->post['password'])){
            return self::returnActionResult([],false,"Please input the password");
        }
        if ($this->post['password']!=$password){
            return self::returnActionResult([],false,"Password Error : ".$password);
        }
        $time=time();
        return self::returnActionResult(
            [
                'Token'=>$time."_".$this->create_password($time)
            ]
        );
    }
}