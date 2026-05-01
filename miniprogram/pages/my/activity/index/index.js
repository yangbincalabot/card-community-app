// pages/my/activity/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    bigData: [],
    joinlist: [],
    joinData: [],
    currentTab: 1,
    delBtnWidth: 180,
    active:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  // tab切换卡
  clickTab: function (e) {
    var that = this;
    if (this.data.currentTab === e.currentTarget.dataset.current) {
      return false;
    } else {
      that.setData({
        currentTab: e.currentTarget.dataset.current,
      })
    }
  },

  addActive:function(){
    this.setData({
      active: true
    })
  },

  closeActive:function () {
    let that = this;
    let _active = that.data.active;
    if (_active === true) {
      that.setData({
        active: false
      })
    }
  },

  getList: function (nextUrl) {
    let that = this;
    let _url = api.ActivityGetMyList;
    if (nextUrl) {
      _url = nextUrl;
    }
    util.post(_url, {})
      .then(response => {
        let _bigData = response.data;
        let _data = response.data.data;
        let _list = that.data.list;
        if (_data && _data.length>0) {
          _list = _list.concat(_data);
        }
        console.log(_bigData);
        that.setData({
          list: _list,
          bigData: _bigData,
        })
      });
  },

  getJoinList: function (nextUrl) {
    let that = this;
    let _url = api.ActivityGetJoinList;
    if (nextUrl) {
      _url = nextUrl;
    }
    util.post(_url, {})
        .then(response => {
          let _bigData = response.data;
          let _data = response.data.data;
          let _list = that.data.joinlist;
          if (_data && _data.length>0) {
            _list = _list.concat(_data);
          }
          console.log(_bigData)
          that.setData({
            joinlist: _list,
            joinData: _bigData,
          })
        });
  },

  realDelete: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    let _key = e.currentTarget.dataset.key;
    wx.showModal({
      title: '删除',
      content: '删除后不可撤销，您确定删除该条信息吗？',
      success(res) {
        if (res.confirm) {
          util.post(api.ActivityDelete, {id:_id})
            .then(response => {
              let _list = that.data.list;
              _list.splice(_key, 1);
              that.setData({
                list: _list
              })
              wx.showToast({ title: '删除成功', icon: 'none', duration: 800 });
            });
        }
      }
    })
    
  },

  cancelActivity: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    console.log(_id)
    wx.showModal({
      title: '您确定取消该该活动吗？',
      content: '已报名并且已付款用户将原路退还报名费，不可逆转，请慎重决定!!!',
      success(res) {
        if (res.confirm) {
          util.post(api.ActivityChangeShelves, { id: _id })
            .then(response => {
              wx.showToast({ title: '操作成功', icon: 'none', duration: 800 });
              setTimeout(() => {
                that.onShow();
              },800);
            });
        }
      }
    })

  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    });
    this.setData({ active: false })
  },

  toCreateUrl: function (e) {
    let that = this;
    let _type = e.currentTarget.dataset.type;
    let _id = e.currentTarget.dataset.id;
    let _url = '../activityCreate/index?id='+_id;
    if (_type === 2) {
      _url = '../meetingCreate/index?id='+_id;
    }
    wx.navigateTo({
      url: _url
    })
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
      list: [],
      bigData: [],
      joinlist: [],
      joinData: []
    });
    that.getJoinList();
    that.getList();
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
    // that.setData({
    //   list: []
    // })
    // that.getList();
    // setTimeout(function () {
    //   wx.stopPullDownRefresh();
    // }, 1000);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    let that = this;
    let _currentTab = that.data.currentTab;
    let _nextUrl = that.data.bigData.next_page_url;
    if (_currentTab == 1) {
      _nextUrl = that.data.joinData.next_page_url;
    }
    if (_nextUrl) {
      if (_currentTab == 1) {
        that.getJoinList(_nextUrl, {});
      } else {
        that.getList(_nextUrl, {});
      }
    } else {
      console.log('没有内容了'); return false;
    }
  },


})