// pages/discover/apply/group/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    group:{},
    fromDetail:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    let _fromDetail = options.fromDetail;
    let _newFrom = 0;
    if (_fromDetail) {
      _newFrom = _fromDetail;
    }
    that.setData({
      fromDetail: _newFrom,
      id: _id
    })
    console.log(_id)
    that.getActivityGroup(_id);
  },

  getActivityGroup: function (id) {
    let that = this;
    let _id = id;
    util.post(api.ActivityDetailGroup, { id, _id })
      .then(response => {
        let _data = response.data.data;
        let param = wx.getStorageSync('activity_apply_group');
        let new_group = {};
        if (param) {
          for (let index in _data) {
            let item = _data[index];
            if (param.indexOf(item.id) != -1) {
              item.checked = true;
            }
            new_group[index] = item;
          }
        } else {
          new_group = _data;
        }
        that.setData({
          group: _data
        })
      });
  },

  checkboxChange: function (e) {
    let that = this;
    let _key = e.currentTarget.dataset.key;
    let _remainder = e.currentTarget.dataset.remainder;
    if (_remainder <= 0) {
      wx.showToast({ title: '该年龄组已满，请选择其他年龄组！', icon: 'none', duration: 800 });
      return  false;
    }
    let _group = that.data.group;
    let _checked = true;
    if (_group[_key].checked) {
      _checked = false;
    }
    _group[_key].checked = _checked;
    that.setData({
      'group': _group
    })
  },

  nextStep: function () {
    let that = this;
    let _list = that.data.group;
    let param = [];
    for (let index in _list) {
      let item = _list[index];
      if (item.checked) {
        param.push(item.id);
      }
    }
    if (param == 0) {
      wx.showToast({ title: '请至少选择一组', icon: 'none', duration: 800 });
      return false;
    }
    wx.setStorageSync('activity_apply_group', param);
    setTimeout(function () {
      let _fromDetail = that.data.fromDetail;
      let _id = that.data.id;
      if (_fromDetail) {
        wx.removeStorageSync('choose_applicant');
        wx.navigateTo({
          url: '../index/index?id=' + _id
        })
      } else {
        wx.navigateBack({
          delta: 1
        })
      }
    }, 100);
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


})