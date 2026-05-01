// pages/card/special/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu_show: false,
    list: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },


  getList: function () {
    let that = this;
    let _search = that.data.search;
    util.post(api.CardSpecialList)
      .then(response => {
        let _data = response.data.data;
        console.log(_data)
        let _list = [];
        if (_data && _data.length > 0) {
          _list = _data
        }
        that.setData({
          list: _list
        })
      });
  },

  handleMovableChange: function (e) {
    var _this = this
    if (e.detail.source === 'friction') {
      if (e.detail.x < -45) {
        _this.showDeleteButton(e)
      } else {
        _this.hideDeleteButton(e)
      }
    } else if (e.detail.source === 'out-of-bounds' && e.detail.x === 0) {
      _this.hideDeleteButton(e)
    }
  },

  handleTouchStart(e) {
    this.startX = e.touches[0].pageX
  },

  handleTouchEnd(e) {
    var _this = this
    if (e.changedTouches[0].pageX < this.startX && e.changedTouches[0].pageX - this.startX <= -30) {
      _this.showDeleteButton(e)
    } else if (e.changedTouches[0].pageX > this.startX && e.changedTouches[0].pageX - this.startX < 30) {
      _this.showDeleteButton(e)
    } else {
      _this.hideDeleteButton(e)
    }
  },

  /**
   * 显示删除按钮
   */
  showDeleteButton: function (e) {
    let _index = e.currentTarget.dataset.index;
    this.setXmove(_index, -87)
  },
  /**
   * 隐藏删除按钮
   */
  hideDeleteButton: function (e) {
    let _index = e.currentTarget.dataset.index;
    this.setXmove(_index, 0)
  },

  /**
     * 设置movable-view位移
     */
  setXmove: function (_index, xmove) {
    let _str = 'list[' + _index + '].xmove';
    this.setData({
      [_str]: xmove
    })
  },

  deleteBtn(e) {
    let _cid = e.currentTarget.dataset.cid;
    util.post(api.SetSpecialUrl, { cid: _cid })
      .then(response => {
        //  提示信息
        wx.showToast({
          title: '操作成功',
          icon: 'none',
          duration: 1500
        });
        setTimeout(() => {
          this.getList();
        }, 1500)
      });
  },

  // 页面跳转
  navigateToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    wx.navigateTo({
      url: url
    });
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


  addPhone: function (e) {
    let _item = e.currentTarget.dataset.item;
    console.log(_item)
    let _post = {};
    if (_item.name) {
      _post.firstName = _item.name;
    }
    if (_item.phone) {
      _post.mobilePhoneNumber = _item.phone;
    }
    if (_item.company_name) {
      _post.organization = _item.company_name;
    }
    if (_item.position) {
      _post.title = _item.position;
    }
    if (_item.email) {
      _post.email = _item.email;
    }
    console.log(_post)
    // 添加到手机通讯录
    wx.addPhoneContact(_post)
  },


  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    that.getList();
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
});