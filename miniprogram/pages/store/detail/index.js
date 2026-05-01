const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const app = getApp()

Page({

  data: {
    imgUrls: [
      '../../../images/store.png',
      '../../../images/store.png',
      '../../../images/store.png'
    ],
    indicatorDots: false,
    autoplay: false,
    interval: 5000,
    duration: 1000,
    id: 0,
    storeInfo: {}
  },

  onLoad: function (options) {
      this.id = options.id;
      this.getStoreDetail();
  },
  getStoreDetail: function () {
      util.get(api.StoreDetailUrl + '?id=' + this.id).then(res => {
          this.setData({
            storeInfo: res.data.data
          })
      })
  },
  onCall: function (event) {
    wx.makePhoneCall({
      phoneNumber: this.data.storeInfo.contact_mobile
    })
  },
  onMap: function () {
    util.getLocation().then(res => {
      wx.openLocation({
        latitude: parseFloat(this.data.storeInfo.latitude),
        longitude: parseFloat(this.data.storeInfo.longitude),
        scale: 18,
        name: this.data.storeInfo.name,
        address: this.data.storeInfo.full_address
      })
    });
  }
});
