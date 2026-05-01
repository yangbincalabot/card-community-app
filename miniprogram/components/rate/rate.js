// components/navbar/index.js
const App = getApp();

Component({
  options: {
    
  },
  /**
   * 组件的属性列表
   */
  properties: {
    max:{
      type:Number,
      value:3
    },
    value_select:{
      type:Number,
      value:-1
    },
    disabled:{
      type:Boolean,
      value:false,
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
   
  },
  lifetimes: {
    attached: function () {
      
    }
  },
  /**
   * 组件的方法列表
   */
  methods: {
    _onClick:function(e){
      if (this.data.disabled){
        return
      }
      let index=e.currentTarget.dataset.index;
      this.setData({
        value_select:index
      })
      this.triggerEvent('markChange', { mark: index }, {})
    },
  }
})