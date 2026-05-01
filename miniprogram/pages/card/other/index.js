const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
      perfectShow: false,
      scrollFlag:false,
        userInfo: {}, // 当前用户
        id: 0, // 当前id
        hidden: true,
        cardDetail: {},
        is_send: false, // 是否传递过名片,
        is_attention: false, // 是否收藏,
        phone: '',
        wechat: '',
        email: '',
        address_title: '',
        footHide : true,
        currentIndex: 0,
        cardRightIn: false,
        cardLeftIn: false,
        timeNum:0,
        pop: false,
        message: '', // 留言内容
        type: 2, // 2-名片广场， 3-分享地址
        share_user_id : 0, // 分享者的id,
        is_scan: false,
        current: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let id = options.id;
        let type = options.type ? options.type : this.data.type;
        let share_user_id = options.share_user_id ? options.share_user_id : this.data.share_user_id;
        let is_scan = options.is_scan ? true : this.data.is_scan;
        if (!id) {
            this.prompt();
            return;
        }
        this.setData({
            id: id,
            type: type,
            share_user_id: share_user_id,
            is_scan: is_scan
        });
        this.getUserInfo();
    },

    toBack: function () {
        wx.navigateBack({
            delta: 1
        })
    },

    onPageScroll: function (e) {
        let _this = this;
        let _userInfo = this.data.userInfo;
        if (!_userInfo || !_userInfo.carte) {
            return false;
        }
        console.log(e.scrollTop)
        if ((e.scrollTop >= 420)&&(!_this.data.perfectShow)) {
            // 动态设置title
            _this.setData({
                scrollFlag: true
            })
        } else {
            _this.setData({
                scrollFlag: false
            })
        }
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

  toEdmit(){
    let _id = this.data.id;
    let _url = '/pages/card/other/index?id=' + _id;
    wx.setStorageSync('otherToUrl', _url);
    setTimeout(() => {
      wx.redirectTo({
        url: '/pages/my/card/editCard/index'
      });
    }, 200);
  },

  closePerfect(){
    let _id = this.data.id;
    this.sendPerfectSubMsg(_id);
    this.setData({
      perfectShow:false,
    })
  },


  sendPerfectSubMsg: function (_id) {
    let _tid = 'jCItW95uAINhEOimxH3l9xeV_3KRuhqFjMdNexzSILA';
    util.subscribeMessage(_tid).then(res => {
      if (res) {
        let param = {};
        param.id = _id;
        util.post(api.SendPerfectSubMsgUrl, param, false)
          .then(response => {
            console.log(response)
          })
      }
    })

  },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        setTimeout(() => {
            this.getCardDetail();
        }, 300);
    },

    playPhone: function (e) {
        let that = this;
        let _phone = e.currentTarget.dataset.phone;
        let _cid = e.currentTarget.dataset.cid;
        if (!_phone) {
            wx.showToast({ title: '该用户未设置设置电话', icon: 'none', duration: 800 });
            return false;
        }
        if (_cid) {
          util.setTalk(_cid);
        }
        wx.makePhoneCall({
            phoneNumber: _phone
        })
    },

    prompt: function () {
        wx.showToast({ title: '页面不存在', icon: 'none', duration: 2000 });
        setTimeout(function () {
            wx.navigateBack({
                delta: 1
            })
        }, 2000);
    },

    getCardDetail: function () {
        let that = this;
        util.get(api.getCardDetailUrl, { id: this.data.id }, false).then(res => {
            let response = res.data.data;
            let userInfo = that.data.userInfo;
            let _footHide = true;
            if (userInfo && userInfo.id === response.carte.uid) {
                _footHide = false;
            }
            this.setData({
                footHide: _footHide,
                cardDetail: response.carte,
                offlineBusinessCard: response.offlineBusinessCard
            });

            // 检查名片收藏状态
            this.getAttentionStatus();

            // 检查名片公开状态
            this.checkCardOpen();

            // 检查名片发送状态
            this.checkReceiveStatus();
        })
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
            wx.showToast({ title: "不能发给自己", icon: 'none', duration: 2000 });
            return;
        }
        if (!cardDetail.id) {
            wx.showToast({ title: "非法操作", icon: 'none', duration: 2000 });
            return;
        }
        if(!userInfo.carte){
            wx.showToast({ title: "请先创建名片", icon: 'none', duration: 2000 });
            setTimeout(() => {
                wx.navigateTo(
                    {
                        url: '../../my/card/editCard/index'
                    }
                );
            }, 2000);
            return;
        }
        util.post(api.SendCardUrl, { card_id: cardDetail.id, message: this.data.message, type: this.data.type, share_user_id: this.data.share_user_id}).then(res => {
            wx.showToast({ title: "发送成功", icon: 'none', duration: 2000 });
            setTimeout(() => {
                this.getAttentionStatus();
                this.setData({
                    is_send: true,
                    pop: false,
                });
            }, 2000)
        });
    },

    getAttentionStatus: function () {
        let _user = this.data.userInfo;
        if (!_user) {
            return false;
        }
        let param = {};
        param.from_id = this.data.id;
        util.post(api.GetAttentionStatusUrl, param, false)
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                let is_attention = false;
                if (_data && _data.status === 1) {
                    is_attention = true;
                }
                this.setData({
                    is_attention: is_attention
                })
            });
    },


    // 关注
    clickAttention: function () {
        let _user = this.data.userInfo;
        // 不能关注自己
        if (_user && _user.id === this.data.cardDetail.uid) {
            wx.showToast({ title: "不能收藏自己", icon: 'none', duration: 2000 });
            return;
        }

        let param = {};
        param.from_id = this.data.cardDetail.id;
        util.post(api.AttentionStoreUrl, param)
            .then(response => {
                //  提示信息
                let is_attention = !this.data.is_attention;
                let msg = is_attention === true ? '收藏成功' : '取消成功';
                this.setData({
                    is_attention: is_attention
                });
                wx.showToast({
                    title: msg,
                    icon: 'none',
                    duration: 1500
                });
            });
    },

    // 检查公开状态
    checkCardOpen: function () {
        let cardDetail = this.data.cardDetail;
        if (cardDetail.open === 1 || (this.data.userInfo && this.data.userInfo.id === cardDetail.id)) {
            this.setData({
                is_open: true,
            });
        } else {
            util.post(api.CardOpenDetail, { card_id: this.data.cardDetail.id }, false).then(res => {
                let response = res.data.data;
                this.setData({
                    is_open: response.is_open,
                });
            });
        }
        wx.hideLoading();

    },

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
          let _userInfo = res.data.data;
          let _perfectShow = this.data.perfectShow;
          if (this.data.is_scan || this.data.share_user_id) {
            if (_userInfo && _userInfo.perfect > 1) {
              _perfectShow = true;
            }
          }
          this.setData({
            userInfo: _userInfo,
            perfectShow: _perfectShow
          });
          
        });
    },


    checkReceiveStatus: function () {
        util.post(api.CheckReceiveStatus, { user_id: this.data.cardDetail.uid }, false).then(res => {
            let response = res.data.data;
            this.setData({
                hidden: false,
                is_send: Boolean(response.status)
            })
        });
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
        let _latitude = parseFloat(cardDetail.latitude);
        let _longitude = parseFloat(cardDetail.longitude);
        if (_latitude && _longitude) {
            wx.openLocation({
                latitude: _latitude,
                longitude: _longitude,
                scale: 18
            })
        } else {
            wx.showToast({ title: "用户暂无设置完整坐标,无法打开地图", icon: 'none', duration: 2000 });
            return;
        }

    },

  previewImages(e) {
    var _this = this
    let index = e.currentTarget.dataset.index
    let list = _this.data.current == 0 ? _this.data.cardDetail.images : '';
    let current = ''
    let urls = []
    if (!list) {
      return false;
    }
    list.forEach(function (value, ind, arr) {
      urls.push(value)
      if (ind == index) {
        current = value
      }

    })
    wx.previewImage({
      current: current,
      urls: urls
    })
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
        let cardDetail = this.data.cardDetail;
        let title = (cardDetail && cardDetail.name) ? cardDetail.name : cardDetail.user.nickname;
        util.post(api.addCarteShareNumUrl, { id: cardDetail.id }).then(res => {});
        return {

            title: title + '的名片',
            // path: 'pages/my/cardCode/cardCodeHandle/index?scene=user_id@' + cardDetail.uid
            // 名片详情的分享地址为当前名片信息
            // 添加参数 type=3 分享类型，分享类型带分享者用户id
            path: 'pages/card/other/index?id=' + cardDetail.id + '&type=3&share_user_id=' + this.data.userInfo.id
        }
    },


  addPhone: function () {
    let _item = this.data.cardDetail;
    // let _post = {};
    // if (_item.name) {
    //   _post.firstName = _item.name;
    // }
    // if (_item.phone) {
    //   _post.mobilePhoneNumber = _item.phone;
    // }
    // if (_item.company_name) {
    //   _post.organization = _item.company_name;
    // }
    // if (_item.position) {
    //   _post.title = _item.position;
    // }
    // if (_item.email) {
    //   _post.email = _item.email;
    // }
    // console.log(_post)
    // 添加到手机通讯录
    // wx.addPhoneContact(_post)
    wx.addPhoneContact({
      firstName: _item.name ? _item.name: '',//联系人姓名
      mobilePhoneNumber: _item.phone ? _item.phone:'',//联系人手机号
      organization: _item.company_name ? _item.company_name: '',
      title: _item.position ? _item.position : '',
      email: _item.email ? _item.email : '',
      success: (res) => {
        console.log(res);
      },
      fail: (error) => {
        console.log(error);
      },
      complete: (res) => {
        console.log(res);
      },
    })
  },

    edit: function () {
        this.setData({
            pop: true
        })
    },
    cancel: function (event) {
        this.setData({
            pop: false
        });
    },
    confirm: function () {
        if(!this.data.message){
            wx.showToast({
                title: '请输入留言内容',
                icon: 'none'
            });
            return;
        }
        this.sendCard();
    },
    inputMessage: function(e){
        let value = e.detail.value;
        this.setData({
            message: value
        });
    },


    // 如果当前详情不是本人，跳转到对应商城
    // 如果当前详情是本人，判断cid是否有值
    gotoGoods: function(){
        let data = this.data;
        // if(data.userInfo.id == data.cardDetail.uid){
        //     if (!data.cardDetail.company_card){
        //         wx.showModal({
        //             title: '提示',
        //             content: '请升级企业用户或者绑定公司',
        //             confirmText: '个人中心',
        //             success: res => {
        //                 if(res.confirm === true){
        //                     wx.navigateTo({
        //                         url: '/pages/my/index/index',
        //                     })
        //                 }
        //             }
        //         });
        //        // return;
        //     }
        // }

        wx.navigateTo({
            url: '/pages/card/newgoods/index/index?cid=' + data.cardDetail.cid,
        })

    }

});