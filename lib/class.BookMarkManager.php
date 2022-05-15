<?php

class BookMark{
    public $bookMarkName;
    public $bookMarkNode;
    public $bookMarkHref;
    public $createTimeStamp;
    public $updateTime;

    const SEPERATOR='========分割线=========';

    public function __construct($fileName)
    {
        if (!file_exists(BOOK_MARK_INDEX.$fileName)){
            touch(BOOK_MARK_INDEX.$fileName);
        }
        $this->bookMarkName=$fileName;
        list($this->bookMarkNode,$this->bookMarkHref,$this->createTimeStamp)=explode(self::SEPERATOR,file_get_contents(BOOK_MARK_INDEX.$fileName));
    }

    public function SavebookMark(){
        empty($this->createTimeStamp) && $this->createTimeStamp=time();
        $this->updateTime=date("Y-m-d H:i:s");
        file_put_contents(BOOK_MARK_INDEX.$this->bookMarkName,implode(self::SEPERATOR,[
            $this->bookMarkNode,
            $this->bookMarkHref,
            $this->createTimeStamp,
            $this->updateTime
        ]));
    }

    public static function UpdateBookMarkName($oldFileName,$newFileName){
        if (file_exists(BOOK_MARK_INDEX.$oldFileName)){
            exec(sprintf("move '%s' '%s'",BOOK_MARK_INDEX.$oldFileName,BOOK_MARK_INDEX.$newFileName));
        }else{
            new self($newFileName);
        }
    }

    public static function GetbookMarks(){
        $files=scandir(BOOK_MARK_INDEX);
        unset($files[0]);
        unset($files[1]);
        $bookMarks=[];
        foreach ($files as $file){
            $bookMarks[]=new self($file);
        }
        return $bookMarks;
    }

    public static function DeletebookMark($fileName){
        if (empty($fileName)){
            return false;
        }
        unlink(BOOK_MARK_INDEX.$fileName);
        return true;
    }
}

class BookMarkManager extends Base{
    public function SaveBookMark(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['Name'] ?? "";
        if (empty($fileName)){
            return self::returnActionResult($this->post,false,"Empty bookmark name");
        }
        $bookMark=new BookMark($fileName);
        $bookMark->bookMarkNode=$this->post['Note'] ?? '';
        $bookMark->bookMarkHref=$this->post['Href'] ?? '';
        $bookMark->SavebookMark();
        return self::returnActionResult();
    }

    public function UpdateBookMarkName(){
        $this->post=json_decode($this->post,1);
        $oldFileName=$this->post['OldName'] ?? "";
        $newFileName=$this->post['NewName'] ?? "";
        if (empty($oldFileName) || empty($newFileName)){
            return self::returnActionResult($this->post,false,"Param Error");
        }
        $oldFileName!=$newFileName && BookMark::UpdateBookMarkName($oldFileName,$newFileName);
        return self::returnActionResult();
    }

    public function DeleteBookMark(){
        $this->post=json_decode($this->post,1);
        $fileName=$this->post['Name'] ?? "";
        BookMark::DeletebookMark($fileName);
        return self::returnActionResult();
    }

    public function BookMarkList(){
        $bookMarkers=BookMark::GetbookMarks();
        $returnData=[];
        usort($bookMarkers,function ($b1,$b2){
            /**
             * @var $b1 BookMark
             * @var $b2 BookMark
             */
            empty($b1->createTimeStamp) && $b1->createTimeStamp=1;
            empty($b2->createTimeStamp) && $b2->createTimeStamp=1;
            return $b2->createTimeStamp-$b1->createTimeStamp;
        });
        foreach ($bookMarkers as $bookMarker){
            /**
             * @var $bookMarker BookMark
             */
            $returnData[]=[
                'Name'=>$bookMarker->bookMarkName,
                'Note'=>$bookMarker->bookMarkNode,
                'Href'=>$bookMarker->bookMarkHref,
                'LastUpdateTime'=>$bookMarker->updateTime
            ];
        }
        return self::returnActionResult(
            [
                'BookMarks'=>$returnData
            ]
        );
    }
}