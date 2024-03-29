<?php

class Calendar extends Base{
    public function InitCalendar(){
        $this->post=json_decode($this->post,1);
        $startDate=empty($this->post['StartTime'])?date("Y-m-01"):$this->post['StartTime'];
        $endDate=date("Y-m-d",strtotime(sprintf("%s +1 month -1 day",$startDate)));
        $returnData=[];
        $startTimeStamp=strtotime($startDate);
        $endTimestamp=strtotime($endDate);
        $preDay=date("N",$startTimeStamp)-1;
        // 设置开始时间
        $startTimeStamp-=$preDay*24*60*60;
        // 设置结束时间
        $finalDay=7-date("N",$endTimestamp)+1;
        $endTimestamp+=$finalDay*24*60*60;
        $index=0;
        $isSeven=0;
        while ($startTimeStamp<$endTimestamp){
            $isSeven++;
            !isset($returnData[$index]) && $returnData[$index]=[];
            $returnData[$index][]=[
                'Date'=>date("n-j",$startTimeStamp)
            ];
            if ($isSeven==7){
                $isSeven=0;
                $index++;
            }
            $startTimeStamp+=24*60*60;
        }
        return self::returnActionResult(
            [
                'Calendar'=>$returnData,
                'StartDate'=>$startDate,
                'EndDate'=>$endDate
            ]
        );
    }
}