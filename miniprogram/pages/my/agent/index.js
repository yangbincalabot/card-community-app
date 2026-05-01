const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
      agentInfo: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
    this.getAgentInfo();
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


  getAgentInfo: function () {
      util.get(api.AgentInfoUrl).then(res => {
          this.setData({
            agentInfo: res.data.data
          })

      })
  },
  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;
    let _name = e.currentTarget.dataset.name;
    let _option_type = e.currentTarget.dataset.option_type;

    if (_option_type == 2) {
      // 店中店没有销售提成
      let _store = this.data.agentInfo.store;
      if (!_store || _store == null) {
        wx.showToast({ title: '请联系管理为您先创建店铺，创建后才可发布活动及回顾。', icon: 'none', duration: 2000 });
        return false;
      }
    }

    if(_name){
      _url = '../account/income/level_two/index';
    }

    if(_url){
      wx.navigateTo({
        url: _url
      })
    }
  },
})