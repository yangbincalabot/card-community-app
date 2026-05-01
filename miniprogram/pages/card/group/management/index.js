// pages/card/group/management/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail: {},
    list:[],
    title: '',
    is_delete: false
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
    that.getDetailList(_id);
  },

  getDetail: function (_id) {
    let that = this;
    util.post(api.GroupShowUrl, {id: _id})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (!_data) {
            that.prompt();
            return false;
          }
          that.setData({
            id: _id,
            title: _data.title,
            detail: _data
          })
        });
  },

  getDetailList: function (_id) {
    let that = this;
    util.post(api.GroupDetailListUrl, {id: _id})
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
    let currentArr = [];
    if (_data && _data.length > 0) {
      for (let index in _data) {
        currentArr.push(_data[index].carte.id);
      }
    }
    console.log(currentArr);
    setTimeout(() => {
      that.setData({
        currentArr: currentArr
      })
    },1000);
  },

  prompt: function () {
    wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 800);
  },

  changeTitle: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      title: _value
    })
  },

  changeDelete: function () {
    let that = this;
    that.setData({
      is_delete: !that.data.is_delete
    })
  },

  operating: function (e) {
    let that = this;
    let is_delete = that.data.is_delete;
    let _cid = e.currentTarget.dataset.id;
    if (is_delete) {
      // wx.showModal({
      //   title: '成员删除',
      //   content: '确定将该成员移出该群组吗？',
      //   success: function (res) {
      //     if (res.confirm) {
      //       that.removeGroup(_cid, _id);
      //     }
      //   }
      // })
      that.removeGroup(_cid);
    } else {
      wx.navigateTo({
        url: '/pages/card/other/index?id=' + _cid,
      })
    }
    
  },

  removeGroup: function (_cid) {
    let that = this;
    let currentArr = that.data.currentArr;
    for (let index in currentArr) {
      if (currentArr[index] == _cid) {
          currentArr.splice(index, 1);
          break;
      }
    }
    setTimeout(() => {
      that.setList(currentArr);
    },100);
  },

  // 目前不用该方法，该方法为直接删除
  // removeGroup: function (_cid, _id) {
  //   let that = this;
  //   util.post(api.GroupRemoveUrl, {id: _id, cid: _cid})
  //       .then(response => {
  //         that.getDetailList(_id);
  //       });
  // },

  deleteGroup: function () {
    let that = this;
    let _id = that.data.id;
    wx.showModal({
      title: '群组删除',
      content: '确定删除该群组吗？',
      success: function (res) {
        if (res.confirm) {
          util.post(api.GroupDeleteUrl, { id: _id})
              .then(response => {
                wx.showToast({title: '删除成功', icon: 'none', duration: 800});
                setTimeout(function () {
                  wx.navigateBack({
                    delta: 1
                  })
                }, 700);
              });
        }
      }
    })
  },

  // 页面跳转
  navigateToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if(url && url !== '#'){
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
    let that = this;
    let _selectArr = wx.getStorageSync('groupTemporarilyArr');
    if (_selectArr) {
      wx.removeStorageSync('groupTemporarilyArr');
      that.setList(_selectArr);
    }
  },

  setList: function (_selectArr) {
    let that = this;
    util.post(api.GroupTemporaryListUrl, { selectArr: _selectArr})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          that.setData({
            list: _data
          });
          that.checkData(_data);
        });
  },

  toSubmit: function () {
    let that = this;
    let _id = that.data.id;
    let _currentArr = that.data.currentArr;
    if (!_id) {
      wx.showToast({ title: '提交错误，信息不存在', icon: 'none', duration: 1000 });
      return false;
    }
    if (_currentArr.length < 1) {
      wx.showToast({ title: '请至少选择一个用户', icon: 'none', duration: 1000 });
      return false;
    }
    that.setData({
      is_submit: true
    });
    let _param = {};
    _param.selectArr = _currentArr;
    _param.title = that.data.title;
    _param.id = _id;
    console.log(_param);
    util.post(api.GroupCreateUrl, _param)
        .then(response => {
          let _data = response.data.data;
          wx.showToast({title: '提交成功', icon: 'none', duration: 800});
          setTimeout(function () {
            wx.navigateBack({
              delta: 1
            })
          }, 700);
        });
    setTimeout(function () {
      that.setData({
        is_submit: false
      })
    }, 2000);
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