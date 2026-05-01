const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    description: '',
    codeImage: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getDescription();
    this.getQrcode();
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


  getQrcode: function () {
      util.get(api.QrcodeGetUrl).then(res => {
          this.setData({
            codeImage: res.data.qrcode
          })
      })
  },
  getDescription: function () {
      util.get(api.GetConfigureUrl + '?name=EXTENSION_DESCRIPTION').then(res => {
          this.setData({
            description: res.data.data.EXTENSION_DESCRIPTION
          })
      })
  },
  downloadFile: function () {
      wx.downloadFile({
          url: this.data.codeImage,
          success: function (res) {
              wx.saveImageToPhotosAlbum({
                filePath: res.tempFilePath,
                success: function (data) {
                    wx.showToast({
                      title: '保存成功',
                      icon: 'success',
                      duration: 2000
                    })
                }
              })
          }
      })
  }
})