# xargs

[参考博客](https://blog.csdn.net/whatday/article/details/105290814)

[toc]

# 1 管道符

这个在Linux的命令中很常见吧，就是将前一个命令的标准输出作为后一个命令的标准输入：

```shell
echo "main" | cat
```

# 2 标准输入，输出，缓冲区，命令行参数

首先看一段C语言的代码：

```c
#include <stdio.h>

int main(int argc,char* argv[]){
    char standerInput[100];
    scanf("%s",&standerInput);
    printf("%s",standerInput);
}
```

其中`argc`和`argv`就是对应的命令行参数。`scanf`和`printf`则分别代表标准输入和标准输出。

所以