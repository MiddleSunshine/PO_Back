# 日期

- d 日期，像是01,31之类的
- j 跟d类似，只是个位数前面没有0

# 星期数

- D 3个字母的星期数，像是Mon，Sun，Sat之类的
- l 小写的完整星期数，比如monday，sunday之类的
- N 数字版的星期几，1-7,1是星期1
- w 数字版的星期几，0-6，0是星期天
- W 本年的第几个星期
# 月份

- S 英语语法下的当前时间是这个月的第几天
- F 完整单词的月份，首字母大写
- M 三个字母的月份，首字母大写
- m 数字版的月份，前面使用0填充
- n 数字版的月份，前面没有0填充
- t 指定月份的天数
# 天

- z 今年的第几天

# 年

- L 是否是闰年
- 0/Y 年份的数字表示
- y 两个数字的年份代表

# 时间

- a am/pm
- A AM/PM
- B 斯沃琪互聯網時間
- g 12小时时间制，左侧不采用0填充
- G 24小时制，左侧不采用0填充
- h 12小时时间制，左侧采用0填充
- H 24小时制，左侧采用0填充
- i 分钟数，左侧采用0填充
- s 秒数，左侧采用0填充
- u/v 豪秒数

# 时区相关的

- e 时区
- I 是否是夏令时
- O 与格林威治的时差，格式 +200
- P 与格林威治的时间，格式 +2:00，比上面多了个冒号
- p 与P相同，只是没有时差的时候，P是返回 +00:00，而p 则是 z
- T 时区缩写
- Z 时差所代表的秒数

# 特定格式

- c ISO 8601 日期格式
- r RFC 2822 日期格式
- U 等同于`time()`