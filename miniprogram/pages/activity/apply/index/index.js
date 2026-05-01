// pages/activity/apply/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail:{},
    mainHidden: true,
    formParams: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _aid = options.aid;
    let _sid = options.sid;
    if (!_aid || !_sid) {
      that.prompt();
      return false;
    }
    that.getDetail(_aid,_sid);
    that.initValidate();
  },

  getDetail: function (_aid, _sid) {
    let that = this;
    util.post(api.ApplyBigDetail, {aid: _aid, sid: _sid})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (!_data || _data.length === 0) {
            that.prompt();
            return false;
          }
          that.setData({
            'formParams.aid': _aid,
            'formParams.sid': _sid,
            'formParams.price': _data.spe.price,
            detail: _data,
            mainHidden: false
          });
        })
  },

  prompt: function () {
    wx.showToast({ title: '信息不全', icon: 'none', duration: 1000 });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 500);
  },

  formSubmit: function (e) {
    let that = this;
    let _is_submit = that.data.is_submit;
    if (_is_submit) {
      wx.showToast({ title: '不要重复提交', icon: 'none', duration: 800 });
      return false;
    }

    let _formData = e.detail.value;
    let _postData = Object.assign(_formData, that.data.formParams);
    if (!that.WxValidate.checkForm(_postData)) {
      //表单元素验证不通过，此处给出相应提示
      let error = that.WxValidate.errorList[0];
      wx.showToast({ title: error.msg, icon: 'none', duration: 800 });
      return false;
    }
    that.setData({
      is_submit: true
    });
    let _price = that.data.formParams.price;
    if (_price > 0) {
      util.post(api.ApplyCreate, _postData)
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (_data.id && _data.pay_status === 3) {
            wx.navigateTo({
              url: '/pages/pay/index/index?id=' + _data.id
            });
            return false;
          }
          wx.showToast({ title: '支付参数报错，请退出重试', icon: 'none', duration: 800 });
          setTimeout(function () {
            wx.navigateBack({
              delta: 1
            })
          }, 800);
        });
      setTimeout(function () {
        that.setData({
          is_submit: false
        })
      }, 2000);
    } else {
      that.freeApply(_postData);
      return false;
    }
    
  },

  freeApply: function (params) {
    let that = this;
    util.post(api.freeApplyUrl, params)
        .then(response => {
          let _data = response.data.data;
          if (_data && _data.id) {
            wx.redirectTo({
              url: '/pages/activity/apply/success/index?id=' + _data.id
            });
          } else {
            wx.showToast({ title: '报名失败', icon: 'none', duration: 800 });
            setTimeout(function () {
              wx.navigateBack({
                delta:1
              })
            }, 800);
          }
        })
  },


  initValidate() {
    let rules = {
      name: {
        required: true,
        maxlength: 30
      },
      phone: {
        required: true,
        tel: true
      },
      company_name: {
        required: true,
        maxlength: 100
      } 
    };

    let message = {
      name: {
        required: "请输入姓名",
        maxlength: "姓名过长"
      },
      phone: {
        required: "请输入手机号",
        tel: '手机号不正确'
      },
      company_name: {
        required: "请输入工作单位",
        maxlength: "工作单位名称过长"
      }

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