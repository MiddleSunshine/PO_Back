# 原型的概念

类比到面向对象中的知识：

```php
# 这里的 A 就是原型
class A{
	public function __construct(){}
}
# 这里的 $a 就是对象实例
$a=new A();
```

所有的原型都有其最开始的原型`Object`

```php
class Object{}

class A extends Object{}
```

# 实例对象，原型，构造函数之间的关联

在面向对象编程中，构造函数隶属于对象，但是在 javascript 中，面向对象可以采用函数来实现，所以构造函数本身也是一个对象。其之间的对应关系为：

![](https://i.imgur.com/zxbLpds.png)




