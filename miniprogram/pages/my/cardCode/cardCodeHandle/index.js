const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
var QQMapWX = require('../../../../qqmap/qqmap-wx-jssdk.js');
var qqmapsdk;
var baseUrl;
// 名片码扫描后直接跳到此页来处理业务逻辑
Page({

    /**
     * 页面的初始数据
     */
    data: {
        userInfo: {},
        user_id: 0, // 扫描进来的用户id
        formData: {
            longitude: '',
            latitude: '',
            address_title: '',
            address_name: '',
            from_user_id: 0,
            exchange_type: 2,
            type: 1,
        },
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        // scene=user_id@129|type@1|id@11
        // 参数以 | 分割，参数名与参数值以 @ 分割
        let scene = decodeURIComponent(options.scene);
        let params = scene.split('|');
        let user_id = 0;
        let exchange_type = 2;
        let type = 1;
        for(let i = 0; i < params.length; i++){
            let param = params[i].split('@');
            switch (param[0]) {
                case 'user_id':
                    user_id = parseInt(param[1]);
                    break;
                case 'exchange_type':
                    exchange_type = parseInt(param[1]);
                    if(exchange_type === 3){
                        type = 3;
                    }
                  break;
                // case 'id':
                //   id = parseInt(param[1]);
                //   break
            }
        }
        this.setData({
            user_id: user_id,
            'formData.from_user_id': user_id,
            'formData.exchange_type': exchange_type,
            'formData.type': type,
        });

        if(Boolean(this.data.userInfo)){
            wx.showLoading({
                title: '加载中',
                mask: true
            });
            this.getUserInfo()
        }
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

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            this.setData({
                userInfo: res.data.data
            });
            this.handleCard();
            wx.hideLoading();
        });
    },

    // 处理名片逻辑
    handleCard: function () {
        if(this.data.user_id === 0){
            wx.redirectTo({
                url: '../../index/index'
            });
            return;
        }
        let userInfo = this.data.userInfo;
        if(userInfo.id === this.data.user_id && userInfo.carte && userInfo.carte.id > 0){
            // 扫自己直接进我的名片(有名片的前提下)
            wx.redirectTo({
                url: '../../../card/other/index?id=' + userInfo.carte.id
            });
            return;
        }

        // 需要打开地图
       // this.getLocation();

        // 不用打开地图
        this.getLocationNotOpenMap();




        // util.getLocation().then(res => {
        //     console.log(res);
        //     this.setData({
        //         'formData.latitude': res.latitude,
        //         'formData.longitude': res.longitude,
        //     });
        //     this.getLocation().then(() => {
        //         //  提交到后台
        //         util.post(api.ResolveCodeUrl, this.data.formData).then(res => {
        //             let response = res.data.data;
        //             wx.redirectTo({
        //                url: '../../../card/myCard/index?id=' + response.id
        //             });
        //         });
        //     }).catch ((err) => {
        //         console.log(err);
        //     });
        // });
    },


    // 不用打开地图
    getLocationNotOpenMap : function(){
        wx.getSetting({
            success : (res) => {
                if(!res.authSetting['scope.userLocation']){
                    wx.authorize({
                        scope: 'scope.userLocation',
                        success: (res) => {
                            this.getQQMapWX();
                        },
                        fail: (res) => {
                            console.log(res);
                            wx.showModal({
                                title: '是否授权当前位置',
                                content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
                                success: (res) => {
                                    if (res.confirm) {
                                        this.isOpenSetting('QQMap');
                                    }
                                }
                            })
                        }
                    })
                }else{
                    this.getQQMapWX();
                }
            },
            fail: (error) => {
                console.log(error);
                this.isOpenSetting('QQMap');
            }
        })
    },


    // 获取当前位置(方案1)
    getQQMapWX : function(){
        wx.getLocation({
            type: 'gcj02', // 返回可以用于wx.openLocation的经纬度
            success:  (res) => {
                this.setData({
                    'formData.latitude': res.latitude,
                    'formData.longitude': res.longitude,
                });

                util.get(api.GetMapKeyUrl).then(res => {
                    let key = res.data.data.mapApiKey;

                    // 实例化API核心类
                    qqmapsdk = new QQMapWX({
                        key: key
                    });
                    qqmapsdk.reverseGeocoder({
                        location: {
                            latitude: this.data.formData.latitude,
                            longitude: this.data.formData.longitude
                        },
                        success: (res) => {
                            console.log(res);
                            this.setData({
                                'formData.address_title': res.result.address,
                                'formData.address_name': res.result.formatted_addresses.recommend,
                            });

                            this.toSave();
                        },
                        fail: function (res) {
                            console.log(res);
                            wx.showToast({
                                title: '获取地理位置信息失败',
                                icon: 'none'
                            })
                        }
                    })
                })

            },
            fail: function(err) {
                console.log(err);
            }
        });
    },



    // 获取当前位置信息(方案2-备用)
    getLocation: function () {
        let that = this;
        wx.getSetting({
            success: (res) => {
                if (!res.authSetting['scope.userLocation']) {
                    wx.authorize({
                        scope: 'scope.userLocation',
                        success(res) {
                            that.setChooseLocation()
                        },
                        fail(res) {
                            console.log(res);
                            wx.showModal({
                                title: '是否授权当前位置',
                                content: '需要获取您的地理位置，请确认授权，否则地图功能将无法使用',
                                success: function (res) {
                                    if (res.confirm) {
                                        that.isOpenSetting();
                                    }
                                }
                            })
                        }
                    })
                } else {
                    that.setChooseLocation()
                }
            },
            fail: (res) => {
                that.isOpenSetting();
            }
        })
    },


    setChooseLocation: function () {
        wx.chooseLocation({
            success: (res) => {
                this.setData({
                    'formData.longitude': res.longitude,
                    'formData.latitude': res.latitude,
                    'formData.address_title': res.address,
                    'formData.address_name': res.name,
                });
                console.log(this.data.formData);
                this.toSave();
            },
            fail:(res) => {
                console.log(res)
            }
        });
    },

    isOpenSetting: function (type) {
        wx.openSetting({
            success: (res) => {
                if (res.authSetting['scope.userLocation']) {
                    wx.removeStorageSync('userLocationStatus');
                    if(type && type === 'QQMap'){
                        this.getQQMapWX();
                    }else{
                        this.setChooseLocation();
                    }
                }
            }
        })
    },

    toSave: function(){
        util.post(api.ResolveCodeUrl, this.data.formData).then(res => {
            let response = res.data.data;
            let _id = response.id;
            wx.redirectTo({
              url: '/pages/card/other/index?id=' + _id + '&is_scan=1'
            });
            // this.checkUserPerfect(response.id);
        });
    },

    checkUserPerfect(_id) {
      let data = this.data.userInfo;
      let _url = '/pages/card/other/index?id=' + _id;
      if (data && data.perfect > 1) {
        wx.showModal({
          title: '完善信息',
          content: '是否前往个人中心完善信息?',
          success: (res) => {
            if (res.confirm) {
              let _tid = 'jCItW95uAINhEOimxH3l9xeV_3KRuhqFjMdNexzSILA';
              util.subscribeMessage(_tid).then(res => {
                wx.setStorageSync('otherToUrl', _url);
                wx.redirectTo({
                  url: '../../card/editCard/index'
                });
              })
            } else if (res.cancel) {
              this.sendActivitySubMsg(_id);
              // wx.redirectTo({
              //   url: _url
              // });
            }
          }
        })
      } else {
        wx.redirectTo({
          url: _url
        });
      }
    },

    
  sendActivitySubMsg: function (_id) {
    let _tid = 'jCItW95uAINhEOimxH3l9xeV_3KRuhqFjMdNexzSILA';
    util.subscribeMessage(_tid).then(res => {
      if (res) {
        let param = {};
        param.id = _id;
        util.post(api.SendPerfectSubMsgUrl, param)
          .then(response => {
            console.log(response)
            wx.redirectTo({
              url: '/pages/card/other/index?id=' + _id
            });
          })
      }
    })

  }


})