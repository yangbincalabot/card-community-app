// pages/card/group/create/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    search: '',
    selectArr: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    that.getList();
  },

  getList: function (_id) {
    let that = this;
    let param = {};
    param.search = that.data.search;
    if (_id) {
      param.gid = _id;
    }
    util.post(api.AttentionChooseUrl, param)
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        that.setData({
          list: _data
        });
        that.checkData(_data);
      });
  },

  // 组装提交数组
  checkData: function (_data) {
    let that = this;
    let currentArr = wx.getStorageSync('undertakeArr');
    if (_data && _data.length > 0 && currentArr) {
      for (let index in _data) {
        let arr = _data[index].datas;
        for (let key in arr) {
          let item = arr[key];
          if (currentArr.indexOf(item.id) >= 0) {
            _data[index].datas[key].selected = true;
          }
        }
      }
    }
    console.log(currentArr);
    if (!currentArr) {
      currentArr = [];
    }
    setTimeout(() => {
      that.setData({
        list: _data,
        selectArr: currentArr
      })
    }, 500);
  },

  changeSelected: function (e) {
    let that = this;
    let _index = e.currentTarget.dataset.index;
    let _son_index = e.currentTarget.dataset.son_index;
    let _status = e.currentTarget.dataset.status;
    let _info_id = e.currentTarget.dataset.info_id;
    let _list = that.data.list;
    let _selectArr = that.data.selectArr;
    _list[_index].datas[_son_index].selected = !_list[_index].datas[_son_index].selected;
    console.log(_status);
    if (_status) {
      _selectArr = that.selectArrSplice(_selectArr, _info_id);
    } else {
      _selectArr.push(_info_id)
    }
    that.setData({
      list: _list,
      selectArr: _selectArr
    })
  },

  selectArrSplice: function (_selectArr, _info_id) {
    if (!_selectArr || !_info_id) {
      return [];
    }
    for (let i = 0; i < _selectArr.length; i++) {
      if (_selectArr[i] == _info_id) {
        _selectArr.splice(i, 1);
        break;
      }
    }
    return _selectArr;
  },

  toSubmit: function () {
    let that = this;
    let _selectArr = this.data.selectArr;
    wx.setStorageSync('undertakeArr', _selectArr);
    setTimeout(() => {
      wx.navigateBack({
        delta: 1
      })
    }, 100);
  },

  changeSearch: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      search: _value
    });
  },

  searchBtn: function (e) {
    let that = this;
    that.getList();
  },

  // 页面跳转
  navigateToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.navigateTo({
        url: url
      });
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
});