<?php

class WhiteBoard extends Base {
    public $whiteboardFileType='json';

    public function Projects(){
        $PID=$this->get['PID'] ?? '';
        if (empty($PID)){
            return self::returnActionResult($this->get,false,'Param Error');
        }
        $directory=File::getFilePath($PID);
        $files=scandir($directory);
        $projects=[];
        foreach ($files as $file){
            if (strpos($file,$this->whiteboardFileType)!==false){
                $projects[]=[
                    'FileName'=>$file,
                    'FilePath'=>$directory.$file
                ];
            }
        }
        return self::returnActionResult(
            [
                'Projects'=>$projects
            ]
        );
    }

    public function ProjectDetail(){
        $this->post=json_decode($this->post,1);
        $projectPath=$this->post['ProjectName'] ?? '';
        if (empty($projectPath)){
            return self::returnActionResult($this->post,false,"Param Error");
        }
        if (!file_exists($projectPath)){
            return self::returnActionResult($this->post,false,"Project Not Exists");
        }
        $document=file_get_contents($projectPath);
        empty($document) && $document=[];
        return self::returnActionResult(
            [
                'document'=>$document
            ]
        );
    }

    public function DeleteProject(){
        $this->post=json_decode($this->post,1);
        $filePath=$this->post['ProjectName'] ?? '';
        if (empty($filePath)){
            return self::returnActionResult($this->post,false,'Param Error');
        }
        unlink($filePath);
        return self::returnActionResult($this->post);
    }

    public function NewProject(){
        $this->post=json_decode($this->post,1);
        $PID=$this->post['PID'] ?? '';
        $projectName=$this->post['ProjectName'] ?? '';
        if (empty($PID) || empty($projectName)){
            return self::returnActionResult($this->post,false,'Param Error');
        }
        File::$fileType=$this->whiteboardFileType;
        $fileContent=File::getFileContent($PID,$projectName);
        if (!empty($fileContent)){
            return self::returnActionResult($this->post,false,'Project Exists');
        }
        $storeData='';
        File::storeFile($PID,$projectName,$storeData);
        return self::returnActionResult($this->post);
    }

    public function SaveProject(){
        $this->post=json_decode($this->post,1);
        $storeFilePath=$this->post['ProjectName'] ?? '';
        $document=$this->post['document'] ?? '';
        if (!File::storeData($storeFilePath,$document)){
            return self::returnActionResult([$storeFilePath],false,"Store File Wrong");
        }
        return self::returnActionResult();
    }
}