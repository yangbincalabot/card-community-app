const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const App = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    companyNav:false,
    scrollTop:10,
    showBlock2:true,
    block_index:0,
    scrollFlag: false, //显示headFix部分
    bottomflag: false,
    windowHeight: '',
    cardDialog: false,
    userInfo: {}, // 当前用户
    id: 0, // 当前id
    hidden: true,
    cardDetail: {},
    is_send: false, // 是否传递过名片,
    is_collect: false, // 是否收藏,
    phone: '',
    wechat: '',
    email: '',
    address_title: '',
    footHide: true,
    currentIndex: 0,
    cardRightIn: false,
    cardLeftIn: false,
    cardUpIn: false,
    mycardDownIn: false,
    cardDownIn: false,
    topBoxShow: false,
    topList: [],
    offlineBusinessCard: [], // 收到的名片
    timeNum: 0,
    isAjax: true, // 是否加载ajax获取名片数据,
    threeDaysReceiveCard: [],
    threeDaysReceiveCardTags: [], // 标记
    isEditStatus: false,
    imgCurrent: 0,
    // windowHeight:'',
    topDH:0,
    top_box: false,
    isHidden: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let id = options.id;
    if (!id) {
      this.prompt();
      return;
    }
    let list = [{
      _id: "dvhhicWM83uNxzrby"
    },
    {
      _id: "M8WuXptrDfvNWiDxt"
    },
    {
      _id: "fjskjfslfjsfj"
    }
    ]
    this.setData({
      id: id,
      list: list
    });
    this.getUserInfo();
    this.getStatistical();
  },
  changeColor(){
    if (this.data.card_color == 2){
        wx.setNavigationBarColor({
          frontColor: '#000000',
          backgroundColor: '#ffffff',
        })
     }
  },
  dialogChange() {
    var _this = this
    _this.setData({
      cardDialog: !_this.data.cardDialog,
      isAjax: _this.data.cardDialog
    })
  },
  closeDialog() {
    this.setData({
      cardDialog: false
    })
  },
  toBack: function () {
    wx.navigateBack({
      delta: 1
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
    // wx.pageScrollTo({
    //   scrollTop: 0,
    // })
    this.setData({
      windowHeight: App.globalData.windowHeight
    })
    setTimeout(() => {
      this.getCardDetail();
    }, 300);
    this.data.setInter = setInterval(() => {
      if (this.data.isAjax) {
        this.getOfflineCard();
      }
    }, 2000);
  },
  //手指触摸动作开始 记录起点X坐标
  touchstart: function (e) {
    let _startX = 0;
    let _startY = 0;
    if (e.changedTouches[0] && e.changedTouches[0].clientX) {
      _startX = e.changedTouches[0].clientX;
    }
    if (e.changedTouches[0] && e.changedTouches[0].clientY) {
      _startY = e.changedTouches[0].clientY;
    }
    this.setData({
      startX: _startX,
      startY: _startY
    })
  },
  //滑动事件处理
  touchmove: function (e) {
    let idx = e.currentTarget.dataset.index;
    let startX = this.data.startX; //开始X坐标
    let startY = this.data.startY; //开始Y坐标
    let touchMoveX = 0; //滑动变化坐标
    let touchMoveY = 0; //滑动变化坐标
    if (e.changedTouches[0] && e.changedTouches[0].clientX) {
      touchMoveX = e.changedTouches[0].clientX;
    }
    if (e.changedTouches[0] && e.changedTouches[0].clientY) {
      touchMoveY = e.changedTouches[0].clientY;
    }
    //获取滑动角度
    let angle = this.angle({
      X: startX,
      Y: startY
    }, {
        X: touchMoveX,
        Y: touchMoveY
      });
    this.closeDialog();
    //滑动超过45度角 return
    // console.log(Math.abs(angle))
    if (Math.abs(angle) > 45) {
      if (touchMoveY > startY) { //上滑
        if (!this.data.topBoxShow){
          
          this.setData({
            topBoxShow: true
          })
        }
      } else {
        if(this.data.topBoxShow){
          this.setData({
            topBoxShow: false
          })
        }
        
      }


    };



  },

  prompt: function () {
    wx.showToast({
      title: '页面不存在',
      icon: 'none',
      duration: 2000
    });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 2000);
  },

  getCardDetail: function () {
    let that = this;
    util.get(api.getCardDetailUrl, {
      id: this.data.id
    }).then(res => {
      let response = res.data.data;
      let userInfo = that.data.userInfo;
      let _footHide = true;
      if (userInfo && userInfo.id === response.carte.uid) {
        _footHide = false;
      }
      this.setData({
        footHide: _footHide,
        cardDetail: response.carte,
        top_box: true,
        card_color: response.carte.card_color ? response.carte.card_color: 1,
        isHidden: false

        // offlineBusinessCard: response.offlineBusinessCard
      });
      this.changeColor();

      // 如果用户没有小程序码，系统生成
      if (response.carte.user && !response.carte.user.qrcode) {
        this.getQrcode();
      }

      // 检查名片收藏状态
      // this.getCollectionStatus();

      // 检查名片公开状态
      //this.checkCardOpen();

      // 检查名片发送状态
      // this.checkReceiveStatus();
    })
  },

  getOfflineCard: function () {
    let that = this;
    let _timeNum = that.data.timeNum;
    let _carte = that.data.cardDetail;
    let _userInfo = that.data.userInfo;
    if (_timeNum > 5) {
      clearInterval(that.data.setInter);
    }
    if (_userInfo && _carte && _carte.uid && _userInfo.id === _carte.uid) {
      wx.request({
        url: api.GetOfflineCardUrl, //仅为示例，并非真实的接口地址
        data: {
          uid: _carte.uid
        },
        header: {
          'Accept': 'application/json',
          'content-type': 'application/json', // 默认值
          'Authorization': 'Bearer ' + wx.getStorageSync('token')
        },
        success(res) {
          let data = res.data.data;
          let tags = [];
          if (data.receiveCartes) {
            for (let [key, value] of data.receiveCartes.entries()) {
              let tag = {
                other_uid: value.from_user.id,
                info_id: value.from_user.carte.id,
                title: value.tag ? value.tag.title : '',
                uid: that.data.userInfo.id,
              };
              tags.push(tag);
            }
          }
          if (res.data.data) {
            that.setData({
              offlineBusinessCard: data.cartes,
              threeDaysReceiveCard: data.receiveCartes ? data.receiveCartes : this.data.receiveCartes,
              threeDaysReceiveCardTags: tags ? tags : this.data.threeDaysReceiveCardTags
            })
          }
        }
      })
    } else {
      _timeNum++;
      that.setData({
        timeNum: _timeNum
      })
    }
    
  },

  markChange(e){
    let _stars = parseInt(e.detail.mark);
    let _cid = e.currentTarget.dataset.cid;
    let _fid = e.currentTarget.dataset.fid;
    if (!_stars || !_cid || !_fid) {
      return false;
    }
    util.post(api.SetStarsUrl, { cid: _cid, stars: _stars, fid: _fid}, false).then(res => {
      console.log(res)
    });

  },

  // 跳转页面
  navigatorToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if (url) {
      wx.navigateTo({
        url: url
      });
    }
  },

  // 传递名片
  sendCard: function () {
    let userInfo = this.data.userInfo;
    let cardDetail = this.data.cardDetail;
    if (userInfo && userInfo.id === cardDetail.uid) {
      wx.showToast({
        title: "不能发给自己",
        icon: 'none',
        duration: 2000
      });
      return;
    }
    if (!cardDetail.id) {
      wx.showToast({
        title: "非法操作",
        icon: 'none',
        duration: 2000
      });
      return;
    }
    if (!userInfo.carte) {
      wx.showToast({
        title: "请先创建名片",
        icon: 'none',
        duration: 2000
      });
      setTimeout(() => {
        wx.navigateTo({
          url: '../../my/card/editCard/index'
        });
      }, 2000);
      return;
    }
    util.post(api.SendCardUrl, {
      card_id: cardDetail.id
    }).then(res => {
      wx.showToast({
        title: "发送成功",
        icon: 'none',
        duration: 2000
      });
      this.getCollectionStatus();
      this.setData({
        is_send: true
      });
    });
  },

  getStatistical() {
    util.post(api.GetCarteStatisticalUrl)
      .then(response => {
        console.log(response)
        let _data = response.data.data;
        if (_data && _data.length > 0) {
          this.setData({
            topList: _data
          })
        }
      });
  },

  getCollectionStatus: function () {
    let _user = this.data.userInfo;
    if (!_user) {
      return false;
    }
    let param = {};
    param.info_id = this.data.id;
    param.type = 1; // 名片类型
    util.post(api.CollectionGetStatusUrl, param)
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        let is_collect = false;
        if (_data && _data.status === 1) {
          is_collect = true;
        }
        this.setData({
          is_collect: is_collect
        })
      });
  },


  // 收藏
  clickCollection: function () {
    let _user = this.data.userInfo;
    // 不能收藏自己
    if (_user && _user.id === this.data.cardDetail.uid) {
      wx.showToast({
        title: "不能收藏自己",
        icon: 'none',
        duration: 2000
      });
      return;
    }

    let param = {};
    param.info_id = this.data.cardDetail.id;
    param.type = 1;
    util.post(api.CollectionUrl, param)
      .then(response => {
        //  提示信息
        let is_collect = !this.data.is_collect;
        let msg = is_collect === true ? '收藏成功' : '取消成功';
        this.setData({
          is_collect: is_collect
        });
        wx.showToast({
          title: msg,
          icon: 'none',
          duration: 1500
        });
        //this.getCollectionStatus(true);
      });
  },

  // 检查公开状态
  checkCardOpen: function () {
    let cardDetail = this.data.cardDetail;
    let phone, wechat, email, address_title = '';
    if (cardDetail.open === 1 || (this.data.userInfo && this.data.userInfo.id === cardDetail.id)) {
      // 公开
      phone = cardDetail.phone;
      wechat = cardDetail.wechat;
      email = cardDetail.email;
      address_title = cardDetail.address_title;
      this.setData({
        phone: phone,
        wechat: wechat,
        email: email,
        address_title: address_title,
      });
    } else {
      util.post(api.CardOpenDetail, {
        card_id: this.data.cardDetail.id
      }).then(res => {
        let response = res.data.data;
        this.setData({
          phone: response.phone,
          wechat: response.wechat,
          email: response.email,
          address_title: response.address_title,
        });
      });
    }
    wx.hideLoading();

  },

  getUserInfo: function () {
    util.get(api.UserIndexUrl).then(res => {
      this.setData({
        userInfo: res.data.data
      });
    });
  },


  checkReceiveStatus: function () {
    util.post(api.CheckReceiveStatus, {
      user_id: this.data.cardDetail.uid
    }).then(res => {
      let response = res.data.data;
      this.setData({
        hidden: false,
        is_send: Boolean(response.status)
      })
    });
  },



  /**
   * 计算滑动角度
   * @param {Object} start 起点坐标
   * @param {Object} end 终点坐标
   */
  angle: function (start, end) {
    var _X = end.X - start.X,
      _Y = end.Y - start.Y
    //返回角度 /Math.atan()返回数字的反正切值
    return 360 * Math.atan(_Y / _X) / (2 * Math.PI)
  },

  /*
   * 复制到剪切板
   */
  clipboard: function (e) {
    util.getClipboard(e);
  },

  openMap: function () {
    let that = this;
    let cardDetail = that.data.cardDetail;
    console.log(cardDetail);
    let _latitude = parseFloat(cardDetail.latitude);
    let _longitude = parseFloat(cardDetail.longitude);
    if (_latitude && _longitude) {
      wx.openLocation({
        latitude: _latitude,
        longitude: _longitude,
        scale: 18
      })
    } else {
      wx.showToast({
        title: "用户暂无设置完整坐标,无法打开地图",
        icon: 'none',
        duration: 2000
      });
      return;
    }

  },


  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    clearInterval(this.data.setInter);
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    clearInterval(this.data.setInter);
  },
  bindchangeX(e){
    console.log(e)
    if(1==e.detail.current){
      console.log('******')
      this.setData({
        showBlock2:false,
        footHide:true,
        companyNav: false,
      })
    }else{
      this.setData({
        showBlock2: true,
        footHide:false,
        companyNav: false,
      })
    }
    this.setData({
      block_index:e.detail.current
    })
  },
  bindchangeY(e){
    if(1==e.detail.current){
      this.setData({
        block_index:2,
        footHide:true,
        companyNav:true,
      })
    }else{
      this.setData({
        footHide: false,
        companyNav: false,
      })
    }
  },
  // scrolltoupper(){
  //   console.log('12121212')
  // },
  scroll(e){
    console.log(e.detail.deltaX)
    console.log(e.detail.deltaY)
  },
  scroll2(e){
    console.log(e.detail.scrollTop)
    if ((e.detail.scrollTop >= 490) && (!this.data.scrollFlag)){
      this.setData({
        scrollFlag:true
      })
    } else if ((e.detail.scrollTop < 490) && (this.data.scrollFlag)){
      this.setData({
        scrollFlag:false
      })
    }
  },
  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    var _this = this
    console.log('用户下拉')
    wx.stopPullDownRefresh()

    let _re_new_visits = wx.getStorageSync('re_new_visits');
    if (!_re_new_visits) {
      wx.setStorageSync('re_new_visits', true);
    }
    if (0 == _this.data.currentIndex) {
      _this.setData({
        topBoxShow: true,
        cardDownIn: true,
        mycardUpIn: false,
      })
    }
    if (2 == _this.data.currentIndex) {
      _this.setData({
        currentIndex: 0,
        cardRightIn: false,
        cardLeftIn: false,
        cardUpIn: false,
        mycardDownIn: true,
      })
      wx.pageScrollTo({
        scrollTop: 0
      })
    }
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    console.log('上拉触底')
    var _this = this
    if (0 == _this.data.currentIndex && (!_this.data.topBoxShow)) {
      _this.setData({
        currentIndex: 2,
        cardRightIn: false,
        cardLeftIn: false,
        cardUpIn: true,
        mycardDownIn: false,
      })
      wx.pageScrollTo({
        scrollTop: 0
      })
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    let cardDetail = this.data.cardDetail;
    let title = (cardDetail && cardDetail.name) ? cardDetail.name : cardDetail.user.nickname;
    return {
      title: title + '的名片',
      // path: 'pages/my/cardCode/cardCodeHandle/index?scene=user_id@' + cardDetail.uid
      // 名片详情的分享地址为当前名片信息
      // path: 'pages/card/other/index?id=' + cardDetail.id
      path: 'pages/card/other/index?id=' + cardDetail.id + '&type=3&share_user_id=' + this.data.userInfo.id
    }
  },

  getQrcode: function (event) {
    wx.showLoading({
      title: '生成中',
    });
    let param = {
      user_id: this.data.cardDetail.uid
    };
    if (event) {
      let type = event.currentTarget.dataset.type;
      if (type && type === 'reset') {
        param.reset = true;
      }
    }
    util.post(api.QrcodeGetUrl, param).then(res => {
      wx.hideLoading();
      this.setData({
        'cardDetail.user.qrcode': res.data
      });
    }).catch(err => {
      wx.hideLoading();
      console.log(err)
    })
  },
  changeEditStatus: function () {
    this.setData({
      isEditStatus: !this.data.isEditStatus
    })
  },
  changeTag: function (e) {
    let index = e.currentTarget.dataset.index;
    let title = e.detail.value;
    let tags = this.data.threeDaysReceiveCardTags;
    tags[index].title = title;
    this.setData({
      threeDaysReceiveCardTags: tags
    });
  },

  // 提交标签修改
  postChange: function () {
    let tags = this.data.threeDaysReceiveCardTags;
    if (tags.length > 0) {
      util.post(api.ChangeTagsUrl, { tags: tags }).then((response) => {
        wx.showToast({
          title: '编辑成功'
        })
        this.setData({
          isEditStatus: false
        })
      })
    } else {
      wx.showToast({
        title: '无修改数据',
        icon: 'none'
      })
    }
  },

  previewImages(e) {
    var _this = this
    let index = e.currentTarget.dataset.index
    let list = _this.data.imgCurrent == 0 ? _this.data.cardDetail.images : '';
    let _current = ''
    let urls = []
    if (!list) {
      return false;
    }
    list.forEach(function (value, ind, arr) {
      urls.push(value)
      if (ind == index) {
        _current = value
      }

    })
    wx.previewImage({
      imgCurrent: _current,
      urls: urls
    })
  },
})