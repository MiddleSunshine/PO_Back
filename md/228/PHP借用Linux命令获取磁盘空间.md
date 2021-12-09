
```php
function getDiskFreeSpace($path,$unit='GB')
    {
				$unit=strtoupper($unit);
        // get free space of mount point by working path.
        $cmd = "df | grep `findmnt -T '$path' | tail -n 1 | awk '{print \$2}'` | awk '{print \$4}' 2>/dev/null";
        $free_disk = @exec($cmd);
        if(preg_match('/\d+/i', $free_disk) > 0) {
            // KB to GB
            $all_unit    = ['B', 'KB', 'MB', 'GB'];
						if(!in_array($unit,$all_unit)){
							   return null;
						}
            $init_unit   = array_search(strtoupper('KB'), $all_unit);
            $target_unit = array_search($unit, $all_unit);
            while(true) {
                if($init_unit == $target_unit) { break; }
                $free_disk /= 1024;
                ++$init_unit;
            }
            $free_disk = round($free_disk, 2);
        } else {
            $free_disk = null;
        }
        return $free_disk;
    }
```

关于上面命令的拆分：

1. `findmnt -T '/home/br/Desktop/shell编程' | tail -n 1 | awk '{print $2}'`，这部分找出当前路径所挂载的磁盘
2. `df | grep "前一步获取的值" | awk '{print $4}'`，得到 `df` 命令中的值，对应位置为剩余空间