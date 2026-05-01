// pages/my/society/audit/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list:[],
    bigData: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let _pid = options.pid;
    this.setData({
      pid: _pid
    })
    this.getList();
  },

  getList: function (_nextUrl) {
    let _url = api.AssociationSubAuditUrl;
    if (_nextUrl) {
        _url = _nextUrl;
    }
    let params = {};
    params.pid = this.data.pid;
    util.get(_url, params)
        .then(response => {
            let _bigData = response.data;
            let _data = response.data.data;
            console.log(_data);
            let _list = this.data.list;
            if (_data && _data.length>0) {
                _list = _list.concat(_data);
            }
            this.setData({
                list: _list,
                bigData: _bigData
            })
        });
},


verify: function(e){
  let id = e.currentTarget.dataset.id;
  wx.showModal({
      title: '审核操作',
      content: '是否让该协会成为您协会的下级？',
      cancelText: '拒绝',
      confirmText: '同意',
      success: res => {
          let status = 0;
          if(res.confirm){
              status = 1;
          }else{
              status = 3;
          }

          util.post(api.AssociationSubAuditVrifyUrl, {aid: id, status}).then(res => {
              wx.showToast({
                  title: '操作成功',
              });

              setTimeout(() => {
                this.init();
              }, 1200);
          })
      }
  })
},

init: function () {
  this.setData({
    list:[],
    bigData: [],
  });
  this.getList();
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
    this.setData({
        list:[],
        bigData: [],
    });
    this.getList();
    setTimeout(function () {
        wx.stopPullDownRefresh();
    }, 1000);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    let _nextUrl = this.data.bigData.next_page_url;
    if (_nextUrl) {
      this.getList(_nextUrl, {});
    } else {
      console.log('没有内容了'); return false;
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})