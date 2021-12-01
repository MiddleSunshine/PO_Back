1. 可以忽略双引号

```shell
echo "It is a test"

echo It is a test
```

2. 打印 " "

就是使用`\`

```shell
echo "\"It is a test\""
```

3. 不换行

```shell
#!/bin/sh
echo -e "OK! \c" # -e 开启转义 \c 不换行
echo "It is a test"
```
