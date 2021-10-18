
```javascript
import {createStore} from 'redux';

// 定义一个 Reducer
const counterReducer=(state,action)=>{
    // action 这里就是 dispatch 中传入的数据
    console.log(action);
    return state;
};

// 定一个 Store
const store=createStore(
    counterReducer,
    window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
);

// Store 分派任务
store.dispatch({
    type:"这里是传递的数据"
});
// 获取 State 的值
console.log(store.getState())
```