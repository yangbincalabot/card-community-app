// pages/activity/meetingDetail/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    navBgColor: 'rgba(255,255,255,0)',
    showZw:false,
    detail:{},
    chooseSize: false,
    mainHidden: true,
    collectionStatus: false,
    tabList: [
      {
        title: '会务概述'
      },
      {
        title: '议程'
      },
      {
        title: '花序社区'
      }
    ],
    current: 0,
    navList: [
      {
        title: '已预约会上换名片方'
      },
      {
        title: '与会方'
      }
    ],
    nav_current: 1,
    footerList: [{ icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E4%BA%8C%E7%BB%B4%E7%A0%81.png', text: '我的名片二维码', url: '/pages/my/cardCode/index' }, { icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E6%89%AB%E7%A0%81%20(1).png', text: '扫码添加名片', url: '/pages/card/camera/index' }, { icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/3.1%E6%8B%8D%E6%91%84.png', text: '拍摄花絮', url: '' }, { icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E5%88%86%E4%BA%AB.png', text: '分享给好友', url:'' }],
    applyed: false,
    options: false,
    reserveList: [],
    selected_num: 0,
    navTop:'',
    navHeight:'',
  },

  onPageScroll: function (e) {
    let _this = this;
    if (e.scrollTop >= 180) {
      // 动态设置title
      _this.setData({
        navBgColor: 'rgba(255,255,255,1)',

      })
    } else if (e.scrollTop >= 90) {
      _this.setData({
        navBgColor: 'rgba(255,255,255,0.5)',

      })
    } else {
      _this.setData({
        navBgColor: 'rgba(255,255,255,0)',

      })
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    if (!_id) {
      that.prompt();
      return false;
    }
    that.getDetail(_id);
    that.getApplyStatus(_id);
    that.setData({
      navTop:wx.getMenuButtonBoundingClientRect().top,
      navHeight:wx.getMenuButtonBoundingClientRect().height
    })
  },

  navBack: function () {
    console.log(1)
    wx.navigateBack({
      delta: 1
    })
  },
  //回主页
  toIndex: function () {
    console.log(1)
    wx.reLaunch({
      url: util.getHomeUrl()
    })
  },

  getDetail: function (_id) {
    let that = this;
    util.post(api.ActivityBigDetail, {id: _id})
        .then(response => {
          let _data = response.data.data;
          if (!_data || _data.length === 0) {
            that.prompt();
            return false;
          }
          console.log(_data);
          let _price = _data.specification[0].price;
          let _remainder = _data.specification[0].remainder;
          let _sid = _data.specification[0].id;
          if (!_price || !_sid) {
            that.prompt();
            return false;
          }
          let _self_uid = 0; 
          if (_data.requset_user && _data.requset_user.id) {
            _self_uid = _data.requset_user.id;
          }
          that.setData({
            id: _id,
            self_uid: _self_uid,
            detail: _data,
            price: _price,
            remainder: _remainder,
            sid: _sid,
            mainHidden: false
          });
          that.getCollectionStatus();
          that.getReserveList(_id);
        });
  },

  prompt: function () {
    wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 800);
  },

  toApply: function () {
    let that = this;
    let _aid = that.data.id;
    let _sid = that.data.sid;
    if (!_aid || !_sid) {
      wx.showToast({ title: '未选择规格或者页面错误，请退出重试', icon: 'none', duration: 1000 });
      return false;
    }
    let _remainder = that.data.remainder;
    if (_remainder > 0) {
      wx.navigateTo({
        url: '../apply/index/index?aid=' + _aid + '&sid=' + _sid
      })
    } else {
      wx.showToast({ title: '该类别报名人数已满，请选择其它规格', icon: 'none', duration: 1000 });
      return false;
    }
  },

  tabsChange: function (e) {
    let cur = e.currentTarget.dataset.index;
    this.setData({
      current: cur
    })
  },
  navsChange(e){
    let cur = e.currentTarget.dataset.index;
    this.setData({
      nav_current: cur
    })
  },
  changeSpe: function (e) {
    let that = this;
    let _sid = e.currentTarget.dataset.sid;
    let _price = e.currentTarget.dataset.price;
    let _remainder = e.currentTarget.dataset.remainder;
    console.log(e);
    that.setData({
      sid: _sid,
      price: _price,
      remainder: _remainder
    })

  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    if (_url) {
      wx.navigateTo({
        url: _url
      })
    }
  },

  // 收藏
  clickCollection:function(){
    let that = this;
    let param = {};
    param.info_id = that.data.detail.id;
    param.type = 3;
    util.post(api.CollectionUrl, param)
        .then(response => {
          that.getCollectionStatus();
        });
  },

  getCollectionStatus: function () {
    let that = this;
    let _user = that.data.detail.requset_user;
    if (!_user) {
      return false;
    }
    let param = {};
    param.info_id = that.data.detail.id;
    param.type = 3;
    util.post(api.CollectionGetStatusUrl, param)
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          let _collectionStatus = false;
          if (_data && _data.status === 1) {
            _collectionStatus = true;
          }
          that.setData({
            collectionStatus:_collectionStatus
          })
        });
  },

  // 显示遮罩层
  showshadow: function (e) {
    if (this.data.chooseSize === false) {
      this.chooseSezi()
    } else {
      this.hideModal()
    }
  },
  // 动画函数
  chooseSezi: function (e) {
    var that = this;
    var animation = wx.createAnimation({
      duration: 500,
      timingFunction: 'linear'
    });
    that.animation = animation;
    animation.step();
    that.setData({
      animationData: animation.export(),
      chooseSize: true,
      clearcart: false
    });
  },
  // 隐藏
  hideModal: function (e) {
    var that = this;
    var animation = wx.createAnimation({
      duration: 500,
      timingFunction: 'linear'
    });
    that.animation = animation;
    animation.step();
    that.setData({
      animationData: animation.export(),
      chooseSize: false
    });
  },

  // 获取该用户报名状态
  getApplyStatus: function (_id) {
    let that = this;
    util.post(api.ApplyStatusUrl, {aid: _id})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (_data && _data.status === 1) {
            that.setData({
              applyed: true
            });
          }
        });
  },

  toBack: function () {
    wx.navigateBack({
      delta: 1
    })
  },

  upService: function () {
    this.setData({
      options: !this.data.options
    })
  },


  playPhone: function (e) {
    let that = this;
    let _phone = e.currentTarget.dataset.phone;
    if (!_phone) {
      wx.showToast({ title: '主办方未设置设置电话', icon: 'none', duration: 800 });
      return false;
    }
    wx.makePhoneCall({
      phoneNumber: _phone
    })
  },

  clickFull: function () {
    wx.showToast({ title: '该类别报名人数已满，请选择其它规格', icon: 'none', duration: 1000 });
    return false;
  },
  previewImage(){
    wx.previewImage({
      current: this.data.detail.cover_image,
      urls: [this.data.detail.cover_image]
    })
  },
  previewImages(e){
    var _this = this
    console.log('*******')
    let index=e.currentTarget.dataset.index
    let list = _this.data.current == 0 ? _this.data.detail.images: _this.data.detail.tricks.images
    // var current = e.target.dataset.src
    console.log(list)
    let current = ''
    let urls = []
    list.forEach(function (value, ind, arr) {
        urls.push(value)
        if (ind == index) {
          current = value
        }
      
    })
    // urls.push(current)
    wx.previewImage({
      current: current,
      urls: urls
    })
  },
  getReserveList: function(_id) {
    util.post(api.ReserveListUrl, {aid: _id})
        .then(response => {
          let _data = response.data.data;
          let _applyList = this.data.detail.apply;
          if (!_applyList || _applyList.length === 0) {
            return false;
          }
          let _selected_num = 0;
          for (let index in _applyList) {
            let item = _applyList[index].carte;
            let _status = false;
            if (_data.indexOf(item.id) > -1) {
              _status = true;
              _selected_num ++;
            }
            _applyList[index].selected = _status;
          }
          this.setData({
            'detail.apply': _applyList,
            selected_num: _selected_num
          });
        });
  },

  reserveStore: function (e) {
    let _cid = e.currentTarget.dataset.id;
    let _aid = this.data.detail.id;
    util.post(api.ReserveStoreUrl, {aid: _aid, cid: _cid})
        .then(response => {
          let _data = response.data.data;
          this.getReserveList(_aid);
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
    this.setData({
      options: false
    })
    
    if (this.data.detail && this.data.detail.id) {
      let _aid = this.data.detail.id;
      this.getReserveList(_aid);
    } 
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})