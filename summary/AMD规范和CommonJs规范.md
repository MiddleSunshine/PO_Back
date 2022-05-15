# 1. 两者的关系

都是加载js文件的方式，只是方式不同，应用的场景也不同。

- CommonJS规范：同步加载，node由于文件都在本地，加载速度快，所以使用的是这个
- AMD规范：异步加载，适合web端，使用`require.js`来实现

# 2. 关键字

CommonJS

- require
- module

AMD规范

- require
- define