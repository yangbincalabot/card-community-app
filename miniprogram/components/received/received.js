// components/received/received.js
Component({
    /**
     * 组件的属性列表
     */
    properties: {
        item: {
            type: Object,
            value: {}
        }
    },

    /**
     * 组件的初始数据
     */
    data: {
      starsImg: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E6%98%9F1%402x%20(1).png',
    },

    /**
     * 组件的方法列表
     */
    methods: {
        accept:function(e){
            let id = e.currentTarget.dataset.id;
            this.triggerEvent('click',{id});
        },
        navigatorToUrl: function(e){
            let item = this.data.item;
            this.triggerEvent('jump', {item});
        }
    },

    lifetimes: {
        ready: function(){
            console.log(this.data.item);
        }
    }
})
