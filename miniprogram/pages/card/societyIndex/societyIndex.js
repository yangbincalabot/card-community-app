// pages/card/societyIndex/societyIndex.js
const util = require('../../../utils/util');
const api = require('../../../config/api.js');
const App = getApp();
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    pageName: String,
    bgColor:String,
    color:String,
    showNav: {
      type: Boolean,
      value: true
    },
    showHome: {
      type: Boolean,
      value: true
    },
    showZw: Boolean,
    id: Number, // 协会id
  },

  /**
   * 组件的初始数据
   */
  data: {
    navHeight:'',
    navTop:'',
    activitylist: [],
    societyDetail: {

    },
  },
  lifetimes: {
    attached: function () {
      this.setData({
        navHeight: App.globalData.navHeight,
        navTop: App.globalData.navTop,
      })
    }
  },
  /**
   * 组件的方法列表
   */
  methods: {
    //回退
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
    toFootprint(){
      wx.navigateTo({
        url:'/pages/card/Footprint/index'
      })
    },
    navigatorToUrl: function (e) {
      console.log(e)
      let url = e.currentTarget.dataset.url;
      if (url) {
          wx.navigateTo({
              url: url
          });
      }
    },
    onLoad(){
      if(this.data.id > 0) {
        this.getSocietyDetails();
        this.getActivityList();
        this.saveFootPrintUrl();
      }else {
        wx.showToast({
          title: '非法操作',
          icon: 'none'
        });
        setTimeout(() => {
          wx.navigateBack();
        }, 1500);
      }
    },

    // 获取协会信息
    getSocietyDetails() {
      util.get(api.GetSosietyDetailsUrl, {aid: this.data.id}).then(res => {
        const data = res.data.data;
        this.setData({
          societyDetail: data
        })
      })
    },

    // 获取推荐活动列表
    getActivityList: function() {
      util.post(api.ActivityAllList, {recommend: 1, aid: this.data.id}, false).then(res => {
        const activitylist = res.data.data;
        this.setData({activitylist,});
      })
    },


    // 保存协会足迹
    saveFootPrintUrl: function(){
      util.post(api.FootPrintUrl, {aid: this.data.id}).then(res => {
        console.log(res)
      })
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
  
  
})