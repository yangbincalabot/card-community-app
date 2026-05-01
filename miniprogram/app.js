//app.js
App({
  globalData: {
    userInfo: null
  },

  onLaunch: function() {
    //导航组件相关参数
    let menuButtonObject = wx.getMenuButtonBoundingClientRect();
    wx.getSystemInfo({
      success: res => {
        let statusBarHeight = res.statusBarHeight,
          navTop = menuButtonObject.top, //胶囊按钮与顶部的距离
          navHeight = statusBarHeight + menuButtonObject.height + (menuButtonObject.top - statusBarHeight) * 2; //导航高度
        this.globalData.navHeight = navHeight;
        this.globalData.navTop = navTop;
        this.globalData.windowHeight = res.windowHeight;
        this.globalData.windowWidth = res.windowWidth
        this.globalData.screenHeight = res.screenHeight
        console.log(this.globalData)
      },
      fail(err) {
        console.log(err);
      }
    })

    let _userInfo = wx.getStorageSync('userInfo');
    if (_userInfo) {
      this.globalData.userInfo = _userInfo;
    }


    // let _currentUrl = this.getCurrentPageUrlWithArgs(1);
    // let _defaultUrl = wx.getStorageSync('defaultUrl');
    // if (_defaultUrl && _currentUrl != _defaultUrl) {
    //   wx.redirectTo({
    //     url: _defaultUrl
    //   })
    // }
  },

  onHide: function() {
    wx.removeStorageSync('is_activity_jumped');

    // let _defaultUrl = this.getCurrentPageUrlWithArgs(1);
    // wx.setStorageSync('defaultUrl', _defaultUrl);

    let _re_new_visits = wx.getStorageSync('re_new_visits');
    if (_re_new_visits) {
      wx.removeStorageSync('re_new_visits');
      this.resetNewVisits();
    }

  },


  resetNewVisits() {
    let ApiRootUrl = 'https://frps.qiangxk.com/api/';
    let _url = ApiRootUrl + 'reset-new-visits';
    wx.request({
      url: _url, //仅为示例，并非真实的接口地址
      method: 'post',
      header: {
        'Accept': 'application/json',
        'content-type': 'application/json', // 默认值
        'Authorization': 'Bearer ' + wx.getStorageSync('token')
      },
      success(res) {}
    })
  },

  getCurrentPageUrlWithArgs(num) {
    let pages = getCurrentPages();
    if (pages.length <= 0) {
      return false;
    }
    let currentPage = pages[pages.length - num];
    let url = currentPage.route;
    let options = currentPage.options;
    let urlWithArgs = `/${url}?`;
    for (let key in options) {
      let value = options[key];
      urlWithArgs += `${key}=${value}&`
    }
    urlWithArgs = urlWithArgs.substring(0, urlWithArgs.length - 1);
    return urlWithArgs
  }

});