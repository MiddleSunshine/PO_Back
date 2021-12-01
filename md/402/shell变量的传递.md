```shell
string="abcd"
string2=$string
string="hello world"
echo ${string2}
```

这里`string`和`string2`不会绑定在一起，最终输出还是

```bash
abcd
```