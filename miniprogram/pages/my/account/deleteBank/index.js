const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      bank_id: '',
      userBank: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      this.data.bank_id = options.id;
      this.getBankInfo();
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



  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;
    if(_url){
      wx.navigateTo({
        url: _url
      })
    }
  },
    getBankInfo: function () {
        util.get(api.BankDetailUrl, {
            id: this.data.bank_id
        }).then(res => {
            this.setData({
                userBank: res.data.data
            })
        })
    },

    deleteBank: function () {
        wx.showModal({
            title: '提示',
            content: '确定删除此银行卡？',
            success: (res) => {
                if (res.confirm) {
                    wx.showLoading({
                        title: '删除中',
                        mask: true,
                        success: () => {
                            util.post(api.BankDeleteUrl, {
                                id: this.data.bank_id
                            }).then(res => {
                                wx.hideLoading();
                                wx.showToast({
                                    title: '删除成功',
                                    duration: 2000
                                });
                                setTimeout(() => {
                                    wx.redirectTo({
                                        url: '../bank/index'
                                    })
                                }, 2000)
                            })
                        }
                    });
                }
            }
        })


    }
})