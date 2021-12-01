```shell
your_name="runoob"
# 使用双引号拼接，"这是原本的字符串"这里开始使用变量
greeting="hello, "$your_name" !"
greeting_1="hello, ${your_name} !"
echo $greeting  $greeting_1
# 使用单引号拼接，注意这里不是在单引号中使用变量！！！
greeting_2='hello, '$your_name' !'
greeting_3='hello, ${your_name} !'
echo $greeting_2  $greeting_3
```