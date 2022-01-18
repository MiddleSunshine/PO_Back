<?php

class CheckPlanConnection extends Base
{
    public static $table = "Plan_Item";

    public function NewConnection()
    {
        $this->post = json_decode($this->post, 1);
        if (empty($this->post['PID']) || empty($this->post['CID'])) {
            return self::returnActionResult($this->post, false, "Error Data");
        }
        if ($this->checkConnectionExists($this->post['PID'], $this->post['CID'])) {
            return self::returnActionResult();
        }
        $this->handleSql(['PID' => $this->post['PID'], 'CID' => $this->post['CID']], 0);
        return self::returnActionResult();
    }

    public function GetConnection()
    {
        $CID = $this->get['CID'];
        if (empty($CID)) {
            return self::returnActionResult($this->get, false, "Error Data");
        }
        $PlanIds = $this->getConnectionByCID($CID);
        if (empty($PlanIds)) {
            return self::returnActionResult();
        }
        $sql = sprintf("select * from %s where ID in (%s)", Plan::$table, implode(',', $PlanIds));
        return self::returnActionResult(
            [
                'Plans' => $this->pdo->getRows($sql)
            ]
        );
    }


    public function DeleteConnection()
    {
        $this->post = json_decode($this->post, 1);
        if (empty($this->post['PID']) || empty($this->post['CID'])) {
            return self::returnActionResult($this->post, false, "Error Data");
        }
        $sql = sprintf("delete from %s where PID=%d and CID=%d;", static::$table, $this->post['PID'], $this->post['CID']);
        $this->pdo->query($sql);
        return self::returnActionResult($this->post, !$this->checkConnectionExists($this->post['PID'], $this->post['CID']));
    }


    public function checkConnectionExists($PID, $CID)
    {
        $sql = sprintf("select * from %s where PID=%d and CID=%d;", static::$table, $PID, $CID);
        return $this->pdo->getFirstRow($sql);
    }

    public function getConnectionByCID($CID)
    {
        if (empty($CID)) {
            return [];
        }
        $sql = sprintf("select CID from %s where CID=%d;", static::$table, $CID);
        return array_keys($this->pdo->getRows($sql, 'PID'));
    }
}
