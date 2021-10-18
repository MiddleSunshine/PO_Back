```javascript
import {createStore, combineReducers, compose, applyMiddleware} from 'redux';
import thunk from "redux-thunk";

// 定一个 Store，并且在其中引入中间件 thunk
const store=createStore(
    counterReducer,
    compose(
        applyMiddleware(...[thunk]),
        window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
    )
);
```

之后就可以在`dispact`时使用异步函数了：

```javascript
store.dispatch((dispatch)=>{
    dispatch({
        type:"同步的 dispatch"
    })
})
```