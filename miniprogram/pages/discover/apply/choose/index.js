// pages/discover/apply/choose/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    selected_num: 0,
    list:[]
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _group_id = options.group_id;
    let _activity_id = options.activity_id;
    let _remainder = options.remainder;
    console.log(_remainder)
    that.setData({
      group_id: _group_id,
      activity_id: _activity_id,
      remainder: _remainder
    })
  },

  checkboxChange: function (e) {
    let that = this;
    let _group_id = that.data.group_id;
    let _activity_id = that.data.activity_id;
    let _key = e.currentTarget.dataset.key;
    let _id = e.currentTarget.dataset.id;
    let _list = that.data.list;
    if (_list[_key].checked) {
      that.realCheck(_key);
      return false;
    }
    let _selected_num = that.data.selected_num;
    let _remainder = that.data.remainder;
    if (_selected_num >= _remainder) {
      wx.showToast({ title: '该组人数已满', icon: 'none', duration: 800 })
      return false;
    }
    let checkOther = that.checkOther(_id);
    if (!checkOther) {
      wx.showToast({ title: '您在其它组已选过改角色，不可重复选择', icon: 'none', duration: 800 })
      return false;
    }
    let param = { group_id: _group_id, id: _id, activity_id: _activity_id};
    util.post(api.ApplicantCheckChoose, param)
      .then(response => {
        that.realCheck(_key);
      });
  },

  checkOther: function (_id) {
    let that = this;
    let _choose_applicant = that.data.choose_applicant;
    if (_choose_applicant) {
      for (let index in _choose_applicant) {
        let item = _choose_applicant[index];
        if (item.indexOf(_id) != -1) {
          return false;
        }
      }
    }
    return true;
  },

  realCheck: function (key) {
    let that = this;
    let _list = that.data.list;
    let _checked = true;
    let _selected_num = that.data.selected_num;
    if (_list[key].checked) {
      _checked = false;
      _selected_num--;
    } else {
      _selected_num++;
    }
    _list[key].checked = _checked;
    that.setData({
      'list': _list,
      selected_num: _selected_num
    })
  },

  getApplicantGetList: function () {
    let that = this;
    util.post(api.ApplicantGetList, {})
      .then(response => {
        let _data = response.data.data;
        console.log(_data)
        if (_data && _data.length>0) {
          that.setData({
            list: _data
          })
        }
        that.setDefaultApplicant();
      });
  },

  remove: function (e) {
    let that = this;
    let _id = e.currentTarget.dataset.id;
    console.log(_id)
    util.post(api.ApplicantDelete, { id: _id})
      .then(response => {
        wx.showToast({ title: '删除成功', icon: 'none', duration: 800 })
        that.getApplicantGetList()
      });
  },


  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    })
  },

  navigateBack: function () {
    let that = this;
    let _list = that.data.list;
    let _group_id = that.data.group_id;
    let data = [];
    for (let index in _list) {
      let item = _list[index];
      if (item.checked) {
        data.push(item.id)
      }
    }
    let _choose_applicant = that.data.choose_applicant;
    let newData = {};
    if (_choose_applicant) {
      newData = _choose_applicant;
    }
    newData[_group_id] = data;
    wx.setStorageSync('choose_applicant', newData);
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
    let that = this;
    that.getApplicantGetList()
  },

  setDefaultApplicant: function () {
    let that = this;
    let _choose_applicant = wx.getStorageSync('choose_applicant');
    let _group_id = that.data.group_id;
    let _list = that.data.list;
    let _default_choose = _choose_applicant[_group_id];
    if (_default_choose) {
      for (let index in _list) {
        let item = _list[index];
        if (_default_choose.indexOf(item.id) != -1) {
          item.checked = true;
        }
      }
    }
    console.log(_default_choose)
    that.setData({
      list: _list,
      choose_applicant: _choose_applicant
    })
  },


  /**
   * 显示删除按钮
   */
  showDeleteButton: function (e) {
    let productIndex = e.currentTarget.dataset.productindex
    this.setXmove(productIndex, -65)
  },

  /**
   * 隐藏删除按钮
   */
  hideDeleteButton: function (e) {
    let productIndex = e.currentTarget.dataset.productindex

    this.setXmove(productIndex, 0)
  },

  /**
   * 设置movable-view位移
   */
  setXmove: function (productIndex, xmove) {
    let list = this.data.list
    list[productIndex].xmove = xmove

    this.setData({
      list: list
    })
  },

  /**
   * 处理movable-view移动事件
   */
  handleMovableChange: function (e) {
    if (e.detail.source === 'friction') {
      if (e.detail.x < -30) {
        this.showDeleteButton(e)
      } else {
        this.hideDeleteButton(e)
      }
    } else if (e.detail.source === 'out-of-bounds' && e.detail.x === 0) {
      this.hideDeleteButton(e)
    }
  },

  /**
   * 处理touchstart事件
   */
  handleTouchStart(e) {
    this.startX = e.touches[0].pageX
  },

  /**
   * 处理touchend事件
   */
  handleTouchEnd(e) {
    if (e.changedTouches[0].pageX < this.startX && e.changedTouches[0].pageX - this.startX <= -30) {
      this.showDeleteButton(e)
    } else if (e.changedTouches[0].pageX > this.startX && e.changedTouches[0].pageX - this.startX < 30) {
      this.showDeleteButton(e)
    } else {
      this.hideDeleteButton(e)
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


})