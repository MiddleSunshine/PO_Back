```c
#include <unistd.h>

int main(){
    // 这里是向标准输出打印信息
    if(write(1,"hello world\n",18)!=18){
        // 这里是向标准错误输出打印信息
        write(2,"write error\n",18);
    }
    return 0;
}

```

异常返回数据情况：

0 写入失败
-1 写入时发生异常
大于0 写入的长度