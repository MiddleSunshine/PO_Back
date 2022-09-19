<?php

class MindNote extends Base{
    const MIND_NOTE_FILE_NAME='MindNoteFile.json';
    const NODE_KEY='nodes';
    const EDGE_KEY='edges';

    public function Save(){
        $this->post=json_decode($this->post,1);
        $pid=$this->post['PID'] ?? -1;
        if (empty($pid)){
            return self::returnActionResult([],false,"Param Error");
        }
        $nodes=$this->post['nodes'] ?? [];
        $edges=$this->post['edges'] ?? [];
        $filePath=File::getFilePath($pid).self::MIND_NOTE_FILE_NAME;
        file_put_contents($filePath,[
            self::NODE_KEY=>$nodes,
            self::EDGE_KEY=>$edges
        ]);
        return self::returnActionResult();
    }

    public function Output(){
        $pid=$this->get['PID'] ?? -1;
        if (empty($pid)){
            return self::returnActionResult([],false,"Param Error");
        }
        $filePath=File::getFilePath($pid).self::MIND_NOTE_FILE_NAME;
        $returnData=[
            self::NODE_KEY=>[],
            self::EDGE_KEY=>[]
        ];
        if (!file_exists($filePath)){
            return self::returnActionResult($returnData);
        }
        $content=file_get_contents($filePath);
        $settings=json_decode($content,1);
        unset($content);
        foreach ($settings[self::NODE_KEY] as &$node){
            // 从数据库重新同步一次数据
            if (isset($node['data']['ID'])){
                $sql=sprintf("select * from %s where ID=%d;",$node['type'],$node['data']['ID']);
                $data=$this->pdo->getFirstRow($sql);
                $node['data']=$data;
            }
        }
        $returnData[self::NODE_KEY]=$settings[self::NODE_KEY];
        $returnData[self::EDGE_KEY]=$settings[self::EDGE_KEY];
        return self::returnActionResult($returnData);
    }
}