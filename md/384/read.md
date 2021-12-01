```c
#include <unistd.h>
#include <stdlib.h>

int main(){
    // 保存输入
    char buffer[128];
    // 记录下输入的长度
    int nread=0;
    // 0：标准输入
    nread=read(0,buffer,128);
    switch (nread)
    {
        case -1:
            write(2,"read error",20);
            break;
        case 0:
            write(2,"read fail",20);
            break;
        default:
            // 这里将输出发送到标准输出
            if (write(1,buffer,nread)!=nread)
            {
                write(2,"write error",20);
            }
            
    }
    return 0;
}

```