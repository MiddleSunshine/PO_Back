<?php

class OKR extends Base{

    public static $table="OKR";

    const STATUS_PROCESSING='processing';
    const STATUS_SUCCESS='success';
    const STATUS_FAIL='fail';

    public function Index(){
        $year=(!empty($this->get['Year'])) ?: date("Y");
        $month=(!empty($this->get['Month'])) ?: date("n");
        return self::returnActionResult(
            [
                'OKR'=>$this->getOKR($year,$month)
            ]
        );
    }

    public function StartOKR(){
        $this->post=json_decode($this->post,1);
        if (empty($this->post['OKR'])){
            return self::returnActionResult(
                $this->post,
                false,
                "Please Input The OKR !"
            );
        }
        $year=date("Y");
        $month=date("n");
        if (!empty($this->getOKR($year,$month))){
            return self::returnActionResult(
                [
                    'year'=>$year,
                    'Month'=>$month
                ],
                false,
                "OKR has been set !"
            );
        }
        $sql=[
            'Year'=>$year,
            'Month'=>$month,
            'OKR'=>$this->post['OKR'],
            'status'=>self::STATUS_PROCESSING,
            'AddTime'=>date("Y-m-d H:i:s")
        ];
        return $this->handleSql($sql,0);
    }

    public function getOKR($year,$month){
        $sql=sprintf("select * from %s where Year='%s' and Month='%s'",static::$table,$year,$month);
        return $this->pdo->getFirstRow($sql);
    }
}