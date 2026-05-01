// pages/camera/index.js
const App = getApp();
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');


Page({


  /**
   * 页面的初始数据
   */
  data: {
    windowHeight: '',
    ctx: {},
    imagePath: '',
    userInfo: {},
    type: 'normal', // normal(如果本人信息不完善，调整到编辑页面，否则保存), self(只保存自己), other(只为他人保存)
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.setData({
      ctx: wx.createCameraContext()
    });
    let type = options.type;
    if (type && ['normal', 'self', 'other'].lastIndexOf(type) !== -1) {
      this.setData({
        type: type
      })
    }
    this.getUserInfo();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var _this = this
    _this.setData({
      windowHeight: App.globalData.screenHeight
    })
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },
  onCamera: function() {
    let ctx = this.data.ctx;
    ctx.takePhoto({
      quality: "high",
      success: (res) => {
        this.setData({
          imagePath: res.tempImagePath
        });
        this.analyticalCard();
      },
      fail: () => {
        wx.showToast({
          title: '系统异常，拍照失败',
          icon: 'none'
        });
        setTimeout(() => {
          wx.hideToast();
        }, 1500)
      },
    });
  },
  takePhoto: function() {
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'],
      sourceType: ['album'],
      success: (res) => {
        this.setData({
          imagePath: res.tempFilePaths[0]
        });
        this.analyticalCard();
      },
      fail: function(error) {
        console.log(error);
      }
    })
  },
  // 解析名片
  analyticalCard: function() {
    wx.showLoading({
      title: '解析中...',
      mask: true
    });
    let code_img = this.data.imagePath;
    let type = this.data.type;
    wx.uploadFile({
      url: api.ScanCardUrl,
      filePath: code_img,
      name: 'file',
      header: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        Authorization: 'Bearer ' + wx.getStorageSync('token')
      },
      formData: {
        type: type
      },
      success: (res) => {
        wx.hideLoading();
        console.log(res);
        if (res.statusCode !== 200) {
          let data = JSON.parse(res.data);
          wx.showToast({
            title: data.message,
            icon: 'none',
            duration: 2000
          });
          return;
        }
        let data = res.data ? JSON.parse(res.data) : null;
        let response = data && data.data ? data.data : null;
        let carteDetail = {};
        if (response) {
          carteDetail = {
            name: response.name,
            company_name: response.company_name,
            phone: response.phone,
            email: response.email,
            position: response.position,
            address_title: response.address_title,
            province: response.province,
            longitude: response.longitude,
            latitude: response.latitude,
            city: response.city,
          };
        }

        let url = '';
        switch (this.data.type) {
          case 'normal':
            // 根据用户名片完善程度跳转不同的页面
            if (parseInt(this.data.userInfo.perfect) === 1) {
              url = '/pages/card/index/index'; // 名片夹地址
            } else {
              url = '/pages/my/card/editCard/index'; // 编辑名片页面
            }
            break;
          case 'self':
            url = '/pages/my/card/editCard/index';
            break;
          case 'other':
            url = '/pages/card/index/index';
            break
        }

        if (!carteDetail.phone) {
          wx.showToast({
            title: '名片解析有误',
            icon: 'none'
          });
          return;
        }

        if (this.data.type == 'other') {
          wx.showModal({
            title: '名片发送',
            content: '将发送一条短信到对方手机号，提醒对方进入小程序完善名片，您确认发送吗',
            success: (res) => {
              if (res.confirm) {
                this.sendNotice(carteDetail, url);
              } else if (res.cancel) {
                // wx.navigateTo({
                //   url: url,
                //   success(res) {
                //     res.eventChannel.emit('initCarte', carteDetail);
                //   }
                // })
                wx.navigateBack({
                  delta: 1,
                  success(res) {
                    res.eventChannel.emit('initCarte', carteDetail);
                  }
                })
              } 
            }
          })
          return false;
        }

      

        wx.navigateTo({
          url: url,
          success(res) {
            res.eventChannel.emit('initCarte', carteDetail);
          }
        })
      },
      fail: function(error) {
        wx.hideLoading();
        console.log(error);
        wx.showToast({
          title: '解析失败',
          icon: 'none',
          duration: 2000
        })
      }
    })
  },

  sendNotice(carteDetail, url) {
    let _name = carteDetail.name;
    let _phone = carteDetail.phone;
    util.get(api.SendNoticeUrl, {
      name: _name,
      phone: _phone
    }).then(response => {
      console.log(response)
      let _data = response.data.data;
      if (_data && _data.msg) {
        wx.showToast({
          title: _data.msg,
          icon: 'none',
          duration: 1500
        })
        setTimeout(() => {
          // wx.navigateTo({
          //   url: url,
          //   success(res) {
          //     res.eventChannel.emit('initCarte', carteDetail);
          //   }
          // })
          wx.navigateBack({
            delta: 1,
            success(res) {
              res.eventChannel.emit('initCarte', carteDetail);
            }
          })
        }, 1500);
      }
    });
  },

  getUserInfo: function() {
    util.get(api.UserIndexUrl).then(res => {
      this.setData({
        userInfo: res.data.data
      });
      wx.hideLoading();
    });
  },
  changeType: function(e) {
    let type = e.currentTarget.dataset.type;
    this.setData({
      type
    });
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
})