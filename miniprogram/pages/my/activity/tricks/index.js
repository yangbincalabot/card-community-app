// pages/my/activity/tricks/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
import WxValidate from "../../../../utils/validate.js";
Page({

  /**
   * 页面的初始数据
   */
  data: {
    content: '',
    formParams: {
      id: '',
      aid: '',
      images: [],
    },
    totalNum: 9,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _aid = options.aid;
    if (!_aid) {
      that.prompt();
      return false;
    }
    that.setData({
      'formParams.aid': _aid
    });
    that.getDetail(_aid);
    that.initValidate();
  },

  prompt: function () {
    wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 800);
  },

  getDetail: function (_aid) {
    let that = this;
    util.post(api.TricksDetail, {aid: _aid})
        .then(response => {
          let _data = response.data.data;
          console.log(_data)
          if (_data) {
            that.setData({
              'formParams.id': _data.id,
              'formParams.images': _data.images,
              content: _data.content,
            });
          }
        });
  },

  moreUploadImage: function (event) {
    let that = this;
    let currentImages = that.data.formParams.images;
    let currentNum = currentImages.length;
    let totalNum = that.data.totalNum;
    if (currentNum == totalNum) {
      wx.showToast({
        title: '最多只能上传' + totalNum + '张图片！',
        duration: 1000
      });
      return false;
    }
    let lastNum = totalNum - currentNum;
    util.multipartFliesUpload(lastNum).then((respond) => {
      let uploadUrlData = respond;
      let _imagesData = currentImages.concat(uploadUrlData);
      console.log(uploadUrlData);
      that.setData({
        'formParams.images': _imagesData,
      });
      wx.showToast({
        title: '上传成功',
        duration: 1000
      });
    }).catch((err) => {
      console.log(err)
      wx.showToast({
        title: '上传失败！',
        duration: 1000
      });
    })
  },

  DeleteImage: function (event) {
    console.log('444');
    let that = this;
    let _key = event.currentTarget.dataset.id;
    let _imagesData = that.data.formParams.images;
    _imagesData.splice(_key, 1);
    that.setData({
      'formParams.images': _imagesData,
    });
  },

  formSubmit: function (e) {
    let that = this;
    let _is_submit = that.data.is_submit;
    if (_is_submit) {
      wx.showToast({title: '不要重复提交', icon: 'none', duration: 800});
      return false;
    }
    let _id = that.data.formParams.id;
    let _url = api.TricksAdd;
    let success_title = '创建成功';
    if (_id) {
      _url = api.TricksEdit;
      success_title = '更新成功';
    }

    setTimeout(function () {
      let _formData = e.detail.value;
      let postData = Object.assign(_formData, that.data.formParams);
      if (!that.WxValidate.checkForm(postData)) {
        //表单元素验证不通过，此处给出相应提示
        let error = that.WxValidate.errorList[0];
        wx.showToast({title: error.msg, icon: 'none', duration: 800});
        return false;
      }
      console.log(postData);
      that.setData({
        is_submit: true
      });
      util.post(_url, postData)
          .then(response => {
            let _data = response.data.data;
            wx.showToast({title: success_title, icon: 'none', duration: 800});
            setTimeout(function () {
              wx.navigateBack({
                delta: 1
              })
            }, 700);
          });
    }, 100);
    setTimeout(function () {
      that.setData({
        is_submit: false
      })
    }, 3000);
  },


  initValidate() {
    let rules = {
      content: {
        required: true
      },
    };

    let message = {
      content: {
        required: "请输入内容"
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