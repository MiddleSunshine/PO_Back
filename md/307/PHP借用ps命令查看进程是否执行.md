```php
function programIsRunning($file){
    $cmd=sprintf("ps aux | grep -v 'grep' | grep -v 'sh' |grep %s",$file);
    $result=[];
    exec($cmd,$result);
    print_r($result);
    return count($result)>1;
}
```