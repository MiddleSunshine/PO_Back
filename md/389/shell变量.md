# 命名规范

- 命名只能使用英文字母，数字和下划线，首个字符不能以数字开头。
- 中间不能有空格，可以使用下划线 _。
- 不能使用标点符号。
- 不能使用bash里的关键字（可用help命令查看保留关键字）。

# 基础使用

```shell
param1="hello world"
echo $param1
echo ${param1}
```

# 使用命令创建变量

```shell
for file in `ls /etc`;do
    echo $file
done
```

或者

for file in $(ls /etc);do
    echo $file
done
