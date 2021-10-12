本质上就是占位符，比如一个shell脚本如下：

```shell
ps $1 | grep $2
```

调用这个shell脚本的时候：

```bash
./1.shell aux php
```

其中：

`$1`就是`aux` ， `$2`就是`php`。

就是这样。