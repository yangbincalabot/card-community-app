// 封装二级 picker组件，二级下标名为`children`
Component({
    /**
     * 组件的属性列表
     */
    properties: {
        // 数据列表
        items: {
            type: Array,
            value: [],
        },

        // 指定key作为显示的内容
        rangeKey: {
            type: String,
            value: ''
        },

        // 默认索引
        index : {
            type: Array,
            value: [0, 0]
        }
    },

    /**
     * 组件的初始数据
     */
    data: {
        select_first_index: 0, // 第一级下标
        select_second_index: 0, // 第二级下标
        dataSheets: [[], []], // picker 显示
    },

    /**
     * 组件的方法列表
     */
    methods: {
        // 改变列
        changeColumnChange: function(event) {
            let data = {
                dataSheets: this.data.dataSheets,
                index:  this.data.index,
            };
            data.index[event.detail.column] = event.detail.value;
            switch (event.detail.column) {
                case 0:
                    data.dataSheets[1] = this.data.items[event.detail.value].children;
                    data.index[1] = 0;
                    break;
                case 1:
                    break;
            }
            this.setData(data);
        },

        // 修改值
        changeValue: function(event){
            // 与父级通讯,根据要求可接收第二个参数
            let index = event.detail.value;
            this.setData({
                index: index
            });

            let first_column = this.data.items[index[0]]; // 第一列信息
            let second_column = '';
            if(first_column.children.length > 0){
                second_column = first_column.children[index[1]]; // 第二列信息
            }

            let eventDetail = {
                first_column: first_column,
                second_column: second_column
            };

            this.triggerEvent('changeValue',eventDetail, event);
        },
    },

    lifetimes: {
        attached: function(){
            // 根据父级获取的数据设置节点
            let first_column = []; // 第一列数据
            let second_column = []; // 第二列数据

            let dataSheets = this.data.dataSheets;

            let items = this.data.items;
            if(items.length > 0){
                first_column = items;
                if(items[0].children.length > 0){
                    second_column = items[0].children;
                }
                dataSheets[0] = first_column;
                dataSheets[1] = second_column;
            }
            this.setData({
                dataSheets: dataSheets
            })

        },

        ready: function(){

        }
    }
})
