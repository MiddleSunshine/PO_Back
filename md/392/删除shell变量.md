# 不能unset readyonly的变量

```shell
#!/bin/bash
myUrl="https://www.google.com"
readonly myUrl
unset myUrl
echo $myUrl
```

```bash
./变量.sh: line 4: unset: myUrl: cannot unset: readonly variable
https://www.google.com
```