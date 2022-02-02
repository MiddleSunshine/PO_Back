<?php

class Calendar extends Base{
    public function InitCalendar(){
        $startDate=date("Y-m-01");
        $endDate=date("Y-m-d",strtotime(sprintf("%s +1 month -1 day",$startDate)));
        $returnData=[];
        $startTimeStamp=strtotime($startDate);
        $endTimestamp=strtotime($endDate);
        $preDay=date("N",$startTimeStamp);
        // 设置开始时间
        $startTimeStamp-=$preDay*24*60*60;
        // 设置结束时间
        $finalDay=7-date("N",$endTimestamp);
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