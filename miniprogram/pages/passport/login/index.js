const util = require('../../../utils/util.js');
const api = require('../../../config/api.js');
//index.js
//获取应用实例
const app = getApp()

Page({
  data: {
    userInfo: {},
    showLoginDialog: false,
    auth_phone: false
  },
  onLoad: function() {
    let _lastUrl = util.getLastUrl();
    if (_lastUrl) {
      wx.removeStorageSync('lastUrl');
      wx.removeStorageSync('lasturl_expiration');
      this.setData({
        lastUrl: _lastUrl
      });
    }

  },

  getPhoneNumber: function (e) {
    let that = this;
    wx.login({
      success: (res) => {
        wx.setStorageSync('token', this.data.token);
        util.getPhoneNumber(e, res.code).then(response => {
          console.log(response);
          if (response) {
            let _lastUrl = that.data.lastUrl;
            if (!_lastUrl) {
              _lastUrl = '/pages/index/index';
            }
            wx.redirectTo({
              url: _lastUrl
            });
            return false;
          }
        });
      }
    })
    

  },

  attemptLogin() {
    let that = this;
    let code = null;
    util.wxLogin().then((response) => {
      code = response;
      let userInfo = this.data.userInfo;
      console.log(userInfo);
      util.wxMiNiLogin({
        code,
        userInfo
      }).then((response) => {
        console.log(response);
        let _user = response.data.user_info;
        wx.setStorageSync('userInfo', _user);
        // if (!user.phone) {
        //     return false;
        // }
        let _lastUrl = that.data.lastUrl;
        // redirectTo只能跳转非tabBar页面，所以这里用判断,tabBar页面使用reLaunch跳转
        // 目前没有定义tabBar页面所以都采用
        // 供需页点赞，未登录也需要登录后再跳转回去redirectTo跳转
        if (!_lastUrl) {
          _lastUrl = '/pages/index/index';
        }
        wx.redirectTo({
          url: _lastUrl
        });
        return false;
        // wx.reLaunch({
        //   url: _lastUrl,   //注意switchTab只能跳转到带有tab的页面，不能跳转到不带tab的页面
        // })

      })
    }).catch((err) => {
      wx.showToast({
        title: '您点击了拒绝授权，将无法使用部分功能！',
        icon: 'none',
        duration: 1000
      });
    })
  },
  getUserInfo(e) {

    // 将用户信息和 code 传给后台
    if (e.detail.userInfo) {
      //用户按了允许授权按钮
      let UserInfo = e.detail.userInfo;
      this.setData({
        userInfo: UserInfo
      });
      this.attemptLogin()
    } else {
      wx.showToast({
        title: '您点击了拒绝授权，将无法使用部分功能！',
        icon: 'none',
        duration: 1000
      });
    }
  },

  notlog() {
    wx.redirectTo({
      url: '/pages/index/index',
    })
  },


  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.login();
  },

  
  login: function () {
    wx.login({
      success: (res) => {
        this.data.code = res.code;
        util.post(api.CodeGetInfoUrl, { code: res.code }, false).then((response) => {
          let _user = response.data.user_info;
          if (_user && !_user.phone) {
            this.setData({
              auth_phone: true
            })
          }
          this.setData({
            token: response.data.token
          })
        });
      }
    })
  },
})
