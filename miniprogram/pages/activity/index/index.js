// pages/activity/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const App=getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    intoScroll:'',
    tabList:[{ title:'推荐' }, { title:'最新' }, { title: '本月' }, { title: '类型'}],
    currentTap: 0,
    under: true,
    type: 0,
    recommend: false,
    month: false,
    list:[],
    bigData: [],
    downShow:false,
    windowHeight:'',
  },
  maskyc(){
     var _this=this
     _this.setData({
        downShow:false
     })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
  },

  tabsChange :function(e) {
    let _curTap = e.currentTarget.dataset.index;
    // wx.pageScrollTo({
    //   scrollTop: 20,
    // })
    this.setData({
      intoScroll: 'list'
    });
    let _currentTap = _curTap;
    let _downShow = false;
    if (_currentTap === 3) {
      if (!this.data.downShow) {
        _downShow = true;
      }
      _currentTap = this.data.currentTap;
    }
    this.setData({
      currentTap: _currentTap,
      downShow: _downShow
    });
    if (_curTap !== 3) {
      this.onShow();
    }
  },

  downHidden: function () {
    this.setData({
      downShow: false
    })
  },

  getList: function (_nextUrl) {
    let that = this;
    let _url = api.ActivityAllList;
    let param = that.getParam();
    if (_nextUrl) {
      _url = _nextUrl;
    }
    util.post(_url, param)
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

  getParam: function () {
    let that = this;
    let _currentTap= that.data.currentTap;
    let param = {};
    let _recommend = false;
    let _month = false;
    if (_currentTap === 0) {
      _recommend = true;
    }else if (_currentTap === 2) {
      _month = true;
    }
    param.type = that.data.type;
    param.recommend = _recommend;
    param.month = _month;
    param.search = that.data.search;
    return param;
  },

  changeType: function (e) {
    let that = this;
    let _type = e.currentTarget.dataset.type;
    that.setData({
      downShow: false,
      type: _type
    });
    that.onShow();
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
    this.setData({
      windowHeight: App.globalData.windowHeight
    })
    that.setData({
      list:[],
      bigData: [],
      intoScroll:'list'
    });
    that.getList();

  },

  toDetail: function (e) {
    let that = this;
    let _type = e.currentTarget.dataset.type;
    let _id = e.currentTarget.dataset.id;
    let _url = "../detail/index?id=" + _id;
    if (_type === 2) {
      _url = "../meetingDetail/index?id=" + _id;
    }
    wx.navigateTo({
      url: _url
    })
  },

  changeSearch: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      search: _value
    });
  },

  searchBtn: function (e) {
    let that = this;
    that.setData({
      list:[],
      bigData: [],
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
    let that = this;
    that.setData({
      list:[],
      bigData: []
    });
    that.getList();
    setTimeout(function () {
      wx.stopPullDownRefresh();
    }, 1000);
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
});