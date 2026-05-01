const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      userBanks: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.showLoading({
      title: '加载中',
    });
    this.getUserBankInfo();
    if(options.type !== undefined && options.type === 'cashOut'){
      wx.setStorageSync('IS_SELECT_BANK', true); // 在钱包主页上清除
      console.log(wx.getStorageSync('IS_SELECT_BANK'));
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
      this.getUserBankInfo();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },


  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;
    let _index = e.currentTarget.dataset.index;
    let is_select_bank = wx.getStorageSync('IS_SELECT_BANK');
    if(is_select_bank === true && _index !== undefined){
      let _current = this.data.userBanks[_index];
      let _card_name = _current.card_name; // 收款人
      let _card_number = _current.card_number.substr(-4); // 尾号
      let _bank_name = _current.bank.name; // 银行名称
      let _select = _bank_name + '(收款人:' + _card_name + ',尾号:' + _card_number + ')';
      let _user_bank_id = _current.id; // 当前user_bank_id
      _url = '../cashOut/index?select=' + _select + '&user_bank_id=' + _user_bank_id;
      wx.redirectTo({
        url: _url
      })
    }
    if(_url){
      // 此处是编辑页面
      wx.navigateTo({
        url: _url
      })
    }
  },
  getUserBankInfo: function () {
    util.get(api.UserBankUrl).then((res) => {
      this.setData({
        userBanks: res.data.data
      });
      wx.hideLoading();
    });
  }
})