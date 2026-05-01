// pages/reviewList/detail/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const html = require('../../../utils/htmlParse/parser.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    reviewDetail:{}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    this.getReviewDetail(_id);
  },

  // 获取活动回顾列表
  getReviewDetail: function (id) {
    let that = this;
    let _id = id;
    util.post(api.ActivityReviewDetail, { id: _id}).then(res => {
      let _data = res.data.data;
      console.log(_data);
      let _content = that.getContent(_data.content);
      html.default.definedCustomTag({ figure: 'div', figcaption: '' });
      let _nodes = html.default.getRichTextJson(_content);
      let _newContent = _nodes.children;
      that.setData({
        contentNode: _newContent,
        reviewDetail: _data
      })
    });
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