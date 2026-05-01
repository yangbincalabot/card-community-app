// pages/card/applyJoin/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');

const TYPE_PERSONAL = 1;
const TYPE_COMPANY = 2;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    current: "0",
    tollIndex: 0,
    isHidden: true,
    inOperation: false,
    aid: 0, // 协会id
    info: {}, // 协会信息
    formData: {
      type: 1, // 默认个人会员
      reason: '', // 理由
      avatar: '', // 会员上传的头像
    }, // 待提交信息
    avatar_url: '', // 后台拼接后的图片
  },
  clickSelect(e) {
    var _this = this;
    const index = e.currentTarget.dataset.index;
    _this.setData({
      current: index,
      'formData.type': index == 0 ? TYPE_PERSONAL : TYPE_COMPANY,
    })
  },
  tollSelect(e) {
    var _this = this
    _this.setData({
      tollIndex: e.currentTarget.dataset.tollindex,
      'formData.role_id': e.currentTarget.dataset.id,
    })
  },
  changeUser: function (event) {
    let _id = event.currentTarget.dataset.id;
    this.setData({
      currentId: _id
    })
    this.realConfirm();
  },

  realConfirm: function () {
    let _id = this.data.currentId;
    if (this.data.currentUser && this.data.currentUser.id == _id) {
      // wx.navigateBack({
      //   delta: 1
      // })
      return false;
    }
    wx.showLoading({
      title: '切换中',
    });
    if (this.data.inOperation) {
      wx.showToast({
        title: '请不要频繁操作',
        icon: 'none',
        duration: 1200
      })
      return false;
    }
    this.setData({
      inOperation: true
    })
    util.get(api.ChangeUserUrl, {
      id: _id
    }).then(response => {
      let _data = response.data.data;
      console.log(_data)
      wx.hideLoading();
      wx.setStorageSync('userInfo', _data.user);
      wx.setStorageSync('token', _data.token);
      // wx.navigateBack({
      //   delta: 1
      // })
    })
    setTimeout(() => {
      this.setData({
        inOperation: false
      })
    }, 3000);
  },


  getUserListUrl: function () {
    util.get(api.GetUserListUrl).then(response => {
      let _data = response.data.data;
      console.log(_data);
      if(!_data.list || _data.list.length == 0){
        wx.showToast({
          title: '请先添加名片',
          icon: 'none',
        });
        return false;
      }

      this.setData({
        currentId: _data.user.id,
        currentUser: _data.user,
        list: _data.list,
        isHidden: false,
        'formData.carte_id': _data.user.carte.id,
      })

    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      aid: options.id,
      'formData.aid': options.id,
    });
    this.getUserListUrl();
    this.getRoleList();
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },

  getRoleList: function () {
    const aid = this.data.aid;
    util.get(api.AssociationInfoUrl, {
      aid
    }).then(res => {
      console.log(res);
      let info = res.data.data.association;
      const roles = info.roles;
      if (!roles || roles.length == 0) {
        wx.showToast({
          title: '该协会未添加角色',
          icon: 'none',
        });
        setTimeout(() => wx.navigateBack(), 1500);
        return;
      }

      this.setData({
        info,
        'formData.role_id': roles[0].id
      });
    })
  },


  UploadImage: function (event) {
    util.fliesUpload().then((respond) => {
      let uploadResponse = JSON.parse(respond.data);
      this.setData({
        'formData.avatar': uploadResponse.storage_path,
        'avatar_url': api.ResourceRootUrl + uploadResponse.relative_url
      });

    }).catch((err) => {
      console.log(err)
    });
  },

  changeReason: function (event) {
    const reason = event.detail.value;
    this.setData({
      'formData.reason': reason,
    })
  },



  formSubmit: function () {
    let reason = this.data.formData.reason;
    if (!reason) {
      wx.showToast({
        title: '请输入理由',
        icon: 'none'
      });
      return;
    }


    let association = this.data.info;
    let userInfo = this.data.currentUser;

    if (association.user_id === userInfo.id) {
      wx.showToast({
        title: '创建者不能申请',
        icon: 'none'
      })
      return;
    }

    if (!userInfo.companyCardStatus && this.data.formData.type == TYPE_COMPANY) {
      wx.showToast({
        title: '请升级企业会员',
        icon: 'none'
      })
      return;
    }

    if (association.user_id === 0) {
      wx.showToast({
        title: '非法操作',
        icon: 'none'
      })
      return;
    }


    const currentRole = this.data.info.roles.find(item => item.id == this.data.formData.role_id);

    // 免费直接跳转提示页面
    let fee = Number(currentRole.fee);
    if (fee > 0) {
      wx.navigateTo({
        url: '../societyPay/societyPay',
        success: res => {
          res.eventChannel.emit('application', {
            association,
            formData: Object.assign(this.data.formData, {fee}),
          })
        }
      })
    } else {
      this.postApplication(reason, () => {
        wx.redirectTo({
          url: '../societyPaySuccess/societyPaySuccess?aid=' + this.data.aid,
        })
      })
    }

  },
  postApplication: function (reason, callback) {
    let aid = this.data.formData.aid;
    util.post(api.ApplicationSocietyUrl, this.data.formData).then(res => {
      if (callback && typeof callback === 'function') {
        callback();
      } else {
        wx.showToast({
          title: '等待审核',
        })
      }
    });
  },

})