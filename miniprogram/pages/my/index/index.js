// pages/my/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const App = getApp();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        navBgColor: 'rgba(255,255,255,0)',
        ResourceRootUrl:api.ResourceRootUrl+'storage/avatars/',
        userInfo: {},
        is_verify: true, // 是否审核状态
        navHeight:'',
        navTop:'',
    },
    navBack: function () {
        wx.navigateBack({
          delta: 1
        })
      },
      //回主页
      toIndex: function () {
        wx.reLaunch({
          url: util.getHomeUrl()
        })
      },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        var _this=this
        _this.setData({
            navTop:wx.getMenuButtonBoundingClientRect().top,
            navHeight:wx.getMenuButtonBoundingClientRect().height
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
      this.getUserInfo()
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


    navigateToUrl:function (e) {
        let that = this;
        let _url = e.currentTarget.dataset.url;
        let _to_type = e.currentTarget.dataset.to_type;
        let _userInfo = that.data.userInfo;
        if (_to_type == 2) {
            if (!_userInfo.carte) {
                wx.setStorageSync('otherToUrl', _url);
                let _tourl = '/pages/my/card/editCard/index';
                if (!_userInfo.phone) {
                    _tourl = '/pages/passport/getPhone/index';
                }
                wx.navigateTo({
                    url: _tourl
                });
                return false;
            }
        }
        wx.navigateTo({
            url: _url
        })

    },

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            console.log(res.data.data);
            this.setData({
                userInfo: res.data.data
            });
            wx.hideLoading();
        });
    },
    upgradeCompanyStatus: function(){
        let userInfo = this.data.userInfo;
        //  非企业用户不允许访问，需要升级企业会员
        if(userInfo.companyCardStatus !== true){
            wx.showToast({
                title: '请先升级企业会员',
                icon: 'none',
                duration: 2000
            });
            setTimeout(() => {
                wx.redirectTo({
                    url: '../card/companyCard/index'
                })
            }, 2000);
            return;
        }

    },

    checkCompanyStatus: function() {
        let userInfo = this.data.userInfo;
        //  非企业用户不允许访问，需要升级企业会员
        if(userInfo.companyCardStatus !== true){
            wx.showToast({
                title: '请先升级企业会员',
                icon: 'none',
                duration: 2000
            });
            setTimeout(() => {
                wx.redirectTo({
                    url: '../card/companyCard/index'
                })
            }, 2000);
            return;
        }
        // 跳转到公司主页
        wx.navigateTo({
            url: '../../card/companyDetail/index?id=' + userInfo.company_card.id
        })
    }

});