// pages/my/activity/spe/index.js
import WxValidate from "../../../../utils/validate";

const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.initValidate();
  },

  formSubmit: function (e) {
    let that = this;
    let postData = e.detail.value;
    if (!that.WxValidate.checkForm(postData)) {
        //表单元素验证不通过，此处给出相应提示
        let error = that.WxValidate.errorList[0];
        wx.showToast({title: error.msg, icon: 'none', duration: 800});
        return false;
    }
    util.post(api.SpecificationAdd, postData)
        .then(response => {
          wx.showToast({title: '创建成功', icon: 'none', duration: 1000});
          let _data = response.data.data;
          let _speArr = wx.getStorageSync('speArr');
          if (!_speArr || _speArr === 'undefind') {
            _speArr = [];
          }
          if (_data.id) {
            _speArr.push(_data.id);
          }
          wx.setStorageSync('speArr', _speArr);
          setTimeout(function () {
            wx.navigateBack({
              delta:1
            });
          }, 500)
        });
  },

  initValidate() {
    let rules = {
      title: {
        required: true,
        maxlength: 50
      },
      price: {
        required: true,
        number:true
      },
      stint: {
        required: true,
        digits:true
      }
    };

    let message = {
      title: {
        required: "请输入标题",
        maxlength: '标题过长'
      },
      price: {
        required: "请输入单价",
        number: "单价必须为一个数字"
      },
      stint: {
        required: "请输入限制名额",
        digits: "限制名额必须为一个整数"
      },

    };
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },
  
})