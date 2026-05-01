const api = require('../../config/api.js');
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      about: {},
      showZw:true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getAbout();
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


  getAbout: function () {
    util.get(api.AboutUrl).then(res => {

      let data = res.data.data;
      // 图片自适应，因为标签不支持css调整样式，所以改成js
      data.content = data.content.replace(/\<img/gi, '<img style="max-width:100%;height:auto" ');
        this.setData({
          about: data
        });
      util.setCurrentNavigationBarTitle(data.title);
    })
  }
})