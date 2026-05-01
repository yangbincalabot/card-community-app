//index.js
//获取应用实例
const api = require('../../config/api.js');
const util = require('../../utils/util.js');
var baseUrl;
Page({

  data: {
    new_care_num: 0,
    nowanshanShow: false,
    menu_show: false,
    imgUrls: [], // banner
    msg: [], // 公告
    activitylist: [],
    activityData: [],
    current_index: 0, // 当前轮播在位置
    showMask:false
  },

  onLoad: function() {
    let that = this;
    this.setHomeUrl();
    that.getBanners();
    that.getCommunals();
    that.getRecommendActivityList();
  },

  setHomeUrl() {
    let _url = "/pages/index/index";
    if (!wx.getStorageSync('HOMEURL') || wx.getStorageSync('HOMEURL') != _url) {
      wx.setStorageSync('HOMEURL', _url);
    }
  },

  toEditcard() {
    wx.navigateTo({
      url: '/pages/my/card/editCard/index',
    })
  },

  getNavData: function() {
    util.get(api.GetNavDataUrl).then(response => {
      let _data = response.data.data;
      this.checkDayEventActivity(_data);
      let _nowanshanShow = this.data.nowanshanShow;
      if (_data.user && _data.user.perfect > 1) {
        _nowanshanShow = true;
      } else {
        _nowanshanShow = false;
      }
      this.setData({
        is_login: _data.is_login,
        activity_num: _data.activity_num,
        new_carte_num: _data.new_carte_num,
        userInfo: _data.user,
        nowanshanShow: _nowanshanShow
      });
    });
  },

  checkDayEventActivity(_data) {
    let _is_activity_jumped = wx.getStorageSync('is_activity_jumped');
    if (_is_activity_jumped) {
      return false;
    }
    if (_data.is_login != 1) {
      return false;
    }
    if (!_data.has_activity) {
      return false;
    }
    let dayEventActivity = _data.dayEventActivity;
    if (dayEventActivity && dayEventActivity.activity) {
      let _id = dayEventActivity.activity.id;
      let _type = dayEventActivity.activity.type;
      let _url = "/pages/activity/detail/index?id=" + _id;
      if (_type === 2) {
        _url = "/pages/activity/meetingDetail/index?id=" + _id;
      }
      wx.setStorageSync('is_activity_jumped', true);
      wx.navigateTo({
        url: _url
      })
    }

  },
  bind_tabbar(e) {
    var _this = this;
    let index = e.currentTarget.dataset.index;
    switch (index) {
      case 'one':
        if (this.data.userInfo && this.data.userInfo.perfect > 1) {
          _this.toEditcard();
        } else {
          _this.toMyCard();
        }
        break;
      case 'two':
        wx.navigateTo({
          url: '/pages/card/index/index',
        });
        break;
      case 'three':
        _this.setData({
          menu_show: !_this.data.menu_show,
          showMask:!_this.data.showMask
        });
        break;
      case 'four':
        wx.navigateTo({
          url: '/pages/activity/index/index',
        });
        break;
      case 'five':
        wx.navigateTo({
          url: '/pages/my/index/index',
        });
        break;
    }
  },
  getRecommendActivityList: function(_nextUrl) {
    let that = this;
    let _url = api.ActivityAllList;
    if (_nextUrl) {
      _url = _nextUrl;
    }
    util.post(_url, {
        recommend: 1
      })
      .then(response => {
        console.log(response)
        let _bigData = response.data;
        let _data = response.data.data;
        console.log(_data);
        let _list = that.data.activitylist;
        if (_data && _data.length > 0) {
          _list = _list.concat(_data);
        }
        that.setData({
          activitylist: _list,
          activityData: _bigData
        })
      });
  },

  toMyCard: function() {
    let that = this;
    util.get(api.UserIndexUrl).then(res => {
      let info = res.data.data;
      if (info && info.carte) {
        wx.navigateTo({
          url: '/pages/card/myCard/index?id=' + info.carte.id
        })
      } else {
        // 没有名片跳转到创建页面
        wx.redirectTo({
          url: '/pages/card/businessCard/index'
        });
      }
    });
  },

  onShow: function() {
    this.getNavData();
  },


  // 获取公告
  getCommunal: function() {

  },


  // 页面跳转
  navigateToUrl: function(event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.navigateTo({
        url: url
      });
    }
  },

  // 页面跳转
  redirectToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.redirectTo({
        url: url
      });
    }
  },

  toDetail: function(e) {
    let that = this;
    let _type = e.currentTarget.dataset.type;
    let _id = e.currentTarget.dataset.id;
    let _url = "/pages/activity/detail/index?id=" + _id;
    if (_type === 2) {
      _url = "/pages/activity/meetingDetail/index?id=" + _id;
    }
    wx.navigateTo({
      url: _url
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    let that = this;
    that.setData({
      activitylist: [],
      activityData: [],
    });
    that.onLoad({});
    this.getNavData();
    setTimeout(function() {
      wx.stopPullDownRefresh();
    }, 1000);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    let _request_user = this.data.requestUser;
    let _shareUrl = '/pages/spread/index/index';
    if (_request_user != '') {
      _shareUrl += '?scene=user_id@' + _request_user.id;
    }
    console.log(_shareUrl);
    return {
      title: 'DueLope',
      path: _shareUrl
    }
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    let that = this;
    let _nextUrl = that.data.activityData.next_page_url;
    if (_nextUrl) {
      that.getRecommendActivityList(_nextUrl, {});
    } else {
      console.log('没有内容了');
      return false;
    }
  },

  getBanners: function() {
    util.get(api.BannerGetUrl, {
      type: 'HOME_BANNER'
    }).then((res) => {
      let response = res.data.data;
      if (response.length > 0) {
        this.setData({
          imgUrls: response
        })
      } else {
        // 默认banner
        let default_banner = [{
          image: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/index_bg.png',
        }];
        this.setData({
          imgUrls: default_banner
        })
      }
    })
  },

  getCommunals: function() {
    util.get(api.CommunalListUrl).then((res) => {
      let response = res.data.data;
      if (response) {
        this.setData({
          msg: response.slice(0, 3), // 默认取3条数据
        })
      }
    });
  },

  communalDetail: function() {
    let communals = this.data.msg;
    if (communals) {
      let current_index = this.data.current_index;
      let id = communals[current_index].id;
      if (id) {
        wx.navigateTo({
          url: '../communal/communalDetail/index?id=' + id
        });
      }
    }
  },

  changeCommunalIndex: function(event) {
    this.setData({
      current_index: event.detail.current
    })
  }

});
