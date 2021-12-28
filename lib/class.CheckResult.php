
<?php

class CheckResult extends Base
{
    public static $table = "check_result";
    public function getCheckResultByDate($year, $month, $day, $hour)
    {
        $where = [];
        if (!empty($year)) {
            $where[] = sprintf('Year=%d ', $year);
        }
        if (!empty($month)) {
            $where[] = sprintf("Month=%d", $month);
        }
        if (!empty($day)) {
            $where[] = sprintf("Day=%d", $day);
        }
        if (!empty($hour)) {
            $where[] = sprintf("Hour=%d", $hour);
        }
        $sql = sprintf("select * from %s where %s", static::$table, implode(" and ", $where));
        return $this->pdo->getRows($sql, 'ListID');
    }

    public function NewResult()
    {
        $this->post = json_decode($this->post, 1);
        if (empty($this->post['ListID'])) {
            return self::returnActionResult($this->post, false, "Wrong Data");
        }
        $this->post['AddTime'] = date("Y-m-d H:i:s");
        $dateMap = [
            'Year' => 'Y',
            'Month' => 'n',
            'Day' => 'j',
            'Hour' => 'G'
        ];
        foreach ($dateMap as $field => $date) {
            if (empty($this->post[$field])) {
                $this->post[$field] = date($date);
            }
        }
        empty($this->post['Result']) && $this->post['Result'] = 0;
        $this->handleSql($this->post, 0);
        return self::returnActionResult($this->post);
    }
}
