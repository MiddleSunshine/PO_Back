```javascript
import React, {useState} from "react";
import {createStore, combineReducers, compose, applyMiddleware} from 'redux';
import thunk from "redux-thunk";
import {Provider,connect} from 'react-redux'

// 定义一个 Reducer
const counterReducer=(state,action)=>{
    // action 这里就是 dispatch 中传入的数据
    return {
        id:1,
        ...state
    }
};

// 定一个 Store
const store=createStore(
    counterReducer,
    compose(
        applyMiddleware(...[thunk]),
        window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
    )
);

// 定义第二个 Reducer

const anotherReducer=(state,action)=>{
    // todo 这种方法在 state==undefined 的情况下，初始化数据会失效
    return {
        ...state,
        id:10
    };
}

// 合并成一个 reducer
const rootReducer=combineReducers({
    counterReducer:counterReducer,
    anotherReducer:anotherReducer
})

// 分派任务
store.dispatch({
    type:"这个参数一定要有",
    payload:{
        id:1
    }
})

// 获取 State 的值
console.log(store.getState())

const mapsStateToProps=(state,ownProps)=>{
    // 这里的数据会被保存到 props 中
    return {
        id:state.id
    };
}

const mapDispatchToProps=(dispatch,ownProps)=>{

}

// 这里是在展示 Hooks 情况下如何通过 connect 将子组件包裹进 redux 中
function SubComponent(props){
    const SubsubItem=connect(mapsStateToProps,mapDispatchToProps)(SubSub);
    const [id,updateId]=useState(10);
    return <div>
        <h1>outside id {id}</h1>
        <SubsubItem
            // 这里是在展示如何在子组件中调用父组件的函数
            updateId={updateId}
        />
    </div>
}

function SubSub(props){
    // props 中会有 dispatch 来分配任务
    const [id,updateId]=useState(props.id);
    return(
        <h1
            onClick={()=>{
                props.updateId(100);
            }}
        >hello world:{id}</h1>
    )
}

class Debug extends React.Component{
    render() {
        // redux 要被包裹在 Provider 中，并且传递一个 store
        return <Provider store={store}>
            <SubComponent />
        </Provider>
    }
}

export default Debug
```