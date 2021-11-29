```shell
# 数组定义方式1
array_name=(value0 value1 value2 value3)
# 数组使用方式
echo ${array_name[0]}
```

```shell
# 数组定义方式2
array_name=(
value0
value1
value2
value3
)
```

```shell
# 可以单独定义
array_name[0]=value0
array_name[1]=value1
array_name[n]=valuen
```