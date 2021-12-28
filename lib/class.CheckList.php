<?php

class CheckList extends Base
{
    public static $table = "CheckList";

    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'Inactive';

    public function List()
    {
        $Year = empty($this->get['Year']) ? date("Y") : $this->get['Year'];
        $Month = empty($this->get['Month']) ? date("n") : $this->get['Month'];
        $Day = empty($this->get['Day']) ? date("j") : $this->get['Day'];
        $Hour = empty($this->get['Hour']) ? date("G") : $this->get['Hour'];
        $checkResult = new CheckResult();
        $results = $checkResult->getCheckResultByDate($Year, $Month, $Day, $Hour);
        $sql = sprintf("select * from %s", static::$table);
        // 排序数据
        $checkList = $this->pdo->getRows($sql, 'PID');
        foreach ($checkList as &$item) {
            if (isset($results[$item['ID']])) {
                $item['Result'] = $results[$item['ID']];
            }
        }
        return self::returnActionResult([
            'List' => array_values($checkList)
        ]);
    }

    public function NewCheckList()
    {
        $this->post = json_decode($this->post, true);
        if (empty($this->post['Title'])) {
            return self::returnActionResult(
                $this->post,
                false,
                "Data Error!"
            );
        }
        switch ($this->post['Status']) {
            case self::STATUS_ACTIVE:
            case self::STATUS_INACTIVE:
                break;
            default:
                $this->post['Status'] = self::STATUS_ACTIVE;
                break;
        }
        $this->post['AddTime'] = date("Y-m-d H:i:s");
        $data = $this->handleSql($this->post, 0, '', true);
        if (!empty($this->post['PID'])) {
            $sql = sprintf("select * from %s where PID=%d", static::$table, $this->post['PID']);
            $historyCheckList = $this->pdo->getFirstRow($sql);
            if (!empty($historyCheckList)) {
                $historyCheckList['PID'] = $data['Data']['ID'];
                $this->handleSql($historyCheckList, $historyCheckList['ID']);
            }
        }
        return self::returnActionResult(
            $this->post
        );
    }

    public function orderCheckList(&$checkList)
    {
        $returnData = [];
        if (!isset($checkList[0])) {
            return $returnData;
        }
        $item = $checkList[0];
        $returnData[] = $item;
        $endlessPrevent = 0;
        while (isset($checkList[$item['ID']]) && $endlessPrevent < 5000) {
            $endlessPrevent++;
            $returnData[] = $checkList[$item['ID']];
            $item = $checkList[$item['ID']];
        }
        return $returnData;
    }
}
