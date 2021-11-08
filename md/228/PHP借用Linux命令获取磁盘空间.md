
```php
public function getDiskSpace($filePath,$logPath=''){
        if (empty($filePath)){
            $filePath="/";
        }
        if (empty($logPath)){
            $logPath=PROJECT_ROOT.DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."diskSpace.log";
        }
        $cmd=sprintf("cd %s && df -hl > %s",$filePath,$logPath);
        exec($cmd);
        return file_get_contents($logPath);
    }
```