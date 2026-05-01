// pages/my/activity/review/create/index.js
const app = getApp();
const api = require('../../../../../config/api.js');
const util = require('../../../../../utils/util.js');
import WxValidate from "../../../../../utils/validate.js";
const html = require('../../../../../utils/htmlParse/parser.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ResourceRootUrl: api.ResourceRootUrl,
    postData:{
      id:'',
      title:'',
      cover_image:'',
      type:2,
      content:''
    },
    is_submit: false
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    if (_id && _id != 'undefined') {
      that.getDetail(_id);
    }
    that.initValidate();
  },

  initValidate(){
    let rules = {
      cover_image: {
        required: true
      },
      title: {
        required: true,
        maxlength: 200
      }
      ,
      content: {
        required: true
      }
    }

    let message = {
      cover_image: {
        required: '请上传图片'
      },
      title: {
        required: "请输入标题",
        maxlength: '标题过长'
      },
      content: {
        required: "请输入内容"
      }
    }
    //实例化当前的验证规则和提示消息
    this.WxValidate = new WxValidate(rules, message);
  },

  changeTitle: function (e) {
    this.setData({
      'postData.title': e.detail.value
    })
  },

  changeContent: function (e) {
    this.setData({
      'postData.content': e.detail.value
    })
  },

  draft: function () {
    let that = this;
    that.reviewSubmit(1);
  },

  release: function () {
    let that = this;
    that.reviewSubmit(2);
  },

  getDetail: function (id) {
    let that = this;
    let _id = id;
    util.post(api.ActivityReviewDetail, { id, _id})
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        let _content = that.getContent(_data.content);
        html.default.definedCustomTag({ figure: 'div', figcaption: '' });
        let _nodes = html.default.getRichTextJson(_content);
        let _newContent = _nodes.children;
        that.setData({
          'postData.id': _data.id,
          'postData.cover_image': _data.cover_image,
          'postData.type': _data.type,
          'postData.title': _data.title,
          'postData.content': _content,
          contentNode: _newContent
        })
      });
  },
  


  reviewSubmit: function (_type) {
    let that = this;
    let _is_submit = that.data.is_submit;
    if (_is_submit) {
      wx.showToast({ title: '不要重复提交', icon: 'none', duration: 300 });
      return false;
    }
    that.setData({
      'postData.type': _type
    })
    let _formData = that.data.postData;
    let _id = _formData.id;
    
    let url = api.ActivityReviewCreate;
    if (_id) {
      url = api.ActivityReviewUpdate;
    }
    if (!this.WxValidate.checkForm(_formData)) {
      //表单元素验证不通过，此处给出相应提示
      let error = this.WxValidate.errorList[0];
      console.log(error);
      wx.showToast({title: error.msg, icon: 'none',duration: 800})
      return false;
    }
    that.setData({
      is_submit: true
    })
    util.post(url, _formData)
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        wx.showToast({
          title: '操作成功',
          icon: 'none',
          duration: 500
        })
        wx.removeStorageSync('activity_content');
        setTimeout(function () {
          wx.navigateBack({
            delta: 1
          })
        }, 500);
      });
    setTimeout(function () {
      that.setData({
        is_submit: false
      })
    }, 3000);
  },

  UploadImage: function (event) {
    let that = this;
    util.fliesUpload().then((respond) => {
      let uploadResponse = JSON.parse(respond.data);
      console.log(uploadResponse);
      that.setData({
        'postData.cover_image': uploadResponse.url
      });
      
    }).catch((err) => {
      console.log(err)
    })
  },

  // 页面跳转
  navigateToContent: function (event) {
    let that = this;
    let url = event.currentTarget.dataset.url;
    let content = that.data.postData.content;
    wx.setStorageSync('activity_content', content);
    setTimeout(function(){
      wx.navigateTo({
        url: '../../content/index'
      });
    },200);
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
    let that = this;
    that.setContent();
  },

  setContent: function () {
    let that = this;
    let _content = wx.getStorageSync('activity_content');
    wx.removeStorageSync('activity_content');
    if (_content) {
      let _new_content = that.getContent(_content);
      html.default.definedCustomTag({ figure: 'div', figcaption: '' });
      let _nodes = html.default.getRichTextJson(_new_content);
      let _newContent = _nodes.children;
      that.setData({
        contentNode: _newContent,
        'postData.content': _content
      })
    }
  },

  getContent: function (content) {
    let that = this;
    let html = content
      .replace(/&nbsp;/g, '\xa0\xa0')
      .replace(/\/>/g, '>')
      .replace(/<img([\s\w"-=\/\.:;]+)((?:(height="[^"]+")))/ig, '<img$1')
      .replace(/<img([\s\w"-=\/\.:;]+)((?:(width="[^"]+")))/ig, '<img$1')
      .replace(/<img([\s\w"-=\/\.:;]+)((?:(style="[^"]+")))/ig, '<img$1')
      .replace(/<img([\s\w"-=\/\.:;]+)((?:(alt="[^"]+")))/ig, '<img$1')
      .replace(/<img([\s\w"-=\/\.:;]+)/ig, '<img$1 style="max-width: 100%;margin:0 auto; height:auto; border-radius: 8Px;"');
    return html;
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

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },


})