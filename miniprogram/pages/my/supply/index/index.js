// pages/my/supply/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list:[],
    bigData: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
  },

  getList: function () {
    let that = this;
    util.post(api.SupplyMyList, {})
        .then(response => {
            let _bigData = response.data;
            let _data = response.data.data;
            console.log(_data);
            let _list = that.data.list;
            if (_data && _data.length>0) {
              _list = _list.concat(_data);
            }
            that.setData({
              list: _list,
              bigData: _bigData
            })
        });
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
    that.setData({
      list:[]
    });
    that.getList();
  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    })
  },

  delete: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '删除',
      content: '你确定删除该条内容吗，无法恢复',
      success (res) {
        if (res.confirm) {
          util.post(api.SupplyDelete, {id:_id})
              .then(response => {
                wx.showToast({ title: '删除成功', icon: 'none', duration: 800 });
                setTimeout(() => {
                  that.onShow();
                },500);
              });
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })

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
    let that = this;
    let _nextUrl = that.data.bigData.next_page_url;
    if (_nextUrl) {
      that.getList(_nextUrl, {});
    } else {
      console.log('没有内容了'); return false;
    }
  },


});