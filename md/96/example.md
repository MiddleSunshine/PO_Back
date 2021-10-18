```javascript
import React from "react";
import {DndProvider,useDrag,useDrop} from "react-dnd";
import {HTML5Backend} from 'react-dnd-html5-backend'


function Box() {
    const [{ isDragging }, drag, dragPreview] = useDrag(() => ({
        // "type" is required. It is used by the "accept" specification of drop targets.
        type: 'BOX',
        // The collect function utilizes a "monitor" instance (see the Overview for what this is)
        // to pull important pieces of state from the DnD system.
        collect: (monitor) => ({
            isDragging: monitor.isDragging()
        })
    }))

    return (
    <div ref={dragPreview} style={{ opacity: isDragging ? 0.5 : 1}}>
        <div role="Handle" ref={drag}>
            <h1 style={{color:isDragging?"red":"blue"}}>hello world</h1>
        </div>
    </div>
)
}

function Bucket() {
    const [{ canDrop, isOver }, drop] = useDrop(() => ({
        // The type (or types) to accept - strings or symbols
        accept: 'BOX',
        // Props to collect
        collect: (monitor) => ({
            isOver: monitor.isOver(),
            canDrop: monitor.canDrop()
        })
    }))

    return (
        <div
            ref={drop}
            role={'Dustbin'}
            style={{ backgroundColor: isOver ? 'red' : 'white' }}
        >
            {canDrop ? 'Release to drop' : 'Drag a box here'}
        </div>
    )
}

class Debug extends React.Component{
    render() {
        return(
            <DndProvider
                backend={HTML5Backend}
            >
                <Box />
                <Bucket />
            </DndProvider>
        )
    }
}

export default Debug
```