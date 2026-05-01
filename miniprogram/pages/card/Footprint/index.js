const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');

let nextPageUrl = api.FootPrintListUrl;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    companies:[
      {
        cid: 175,
        company: {
          address_name: "宝通大厦",
          address_title: "广东省深圳市宝安区宝民一路215号",
          company_name: "约范信息技术（深圳）有限公司",
          contact_number: "18927421552",
          created_at: "2020-07-07 10:25:48",
          id: 175,
          images: ["https://yf.youfun.shop/storage/uploads/2020/07/07/DqYKEUAKn6pCcWna4oqgPu4njUGXhaC8rw7op6ya.jpg"],
          industry: {
            created_at: "2019-12-10 03:40:29",
            id: 72,
            name: "电信、广播电视和卫星传输服务",
            parent_id: 71,
            sort: 72,
            updated_at: "2019-12-10 03:40:29",
          },
          industry_id: 72,
          industry_name: "电信、广播电视和卫星传输服务",
          initial: "Y",
          introduction: "公司信息",
          latitude: "22.566887",
          logo: "https://yf.youfun.shop/storage/avatars/default.jpg",
          longitude: "113.89609",
          role_id: 0,
          role_sort: 9999,
          status: 1,
          uid: 285,
          updated_at: "2020-09-03 11:42:49",
          visits: 3,
          website: "www.youfun.shop"
        },
        created_at: "2020-07-07 11:19:59",
        deleted_at: null,
        desc: "我饿行了他我默默努力咯的",
        fee: "0.00",
        id: 5,
        image: "https://yf.youfun.shop/storage/uploads/2020/07/07/eCVRCrcuGFDkjBBsQd5pNZShTbP396M5NZUQ4Ev2.jpg",
        images: [],
        name: "我的协会",
        pat: 2,
        pid: 0,
        remark: null,
        status: 2,
        status_text: "审核成功",
        updated_at: "2020-07-07 14:06:30",
        user_id: 285,
      },
      {
        cid: 175,
        company: {
          address_name: "宝通大厦",
          address_title: "广东省深圳市宝安区宝民一路215号",
          company_name: "约范信息技术（深圳）有限公司",
          contact_number: "18927421552",
          created_at: "2020-07-07 10:25:48",
          id: 175,
          images: ["https://yf.youfun.shop/storage/uploads/2020/07/07/DqYKEUAKn6pCcWna4oqgPu4njUGXhaC8rw7op6ya.jpg"],
          industry: {
            created_at: "2019-12-10 03:40:29",
            id: 72,
            name: "电信、广播电视和卫星传输服务",
            parent_id: 71,
            sort: 72,
            updated_at: "2019-12-10 03:40:29",
          },
          industry_id: 72,
          industry_name: "电信、广播电视和卫星传输服务",
          initial: "Y",
          introduction: "公司信息",
          latitude: "22.566887",
          logo: "https://yf.youfun.shop/storage/avatars/default.jpg",
          longitude: "113.89609",
          role_id: 0,
          role_sort: 9999,
          status: 1,
          uid: 285,
          updated_at: "2020-09-03 11:42:49",
          visits: 3,
          website: "www.youfun.shop"
        },
        created_at: "2020-07-07 11:19:59",
        deleted_at: null,
        desc: "我饿行了他我默默努力咯的",
        fee: "0.00",
        id: 5,
        image: "https://yf.youfun.shop/storage/uploads/2020/07/07/eCVRCrcuGFDkjBBsQd5pNZShTbP396M5NZUQ4Ev2.jpg",
        images: [],
        name: "我的协会",
        pat: 2,
        pid: 0,
        remark: null,
        status: 2,
        status_text: "审核成功",
        updated_at: "2020-07-07 14:06:30",
        user_id: 285,
      }
    ],

    list: [],
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
    this.getFootPrintList();
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
    nextPageUrl = api.FootPrintListUrl;
    this.getFootPrintList(() => wx.stopPullDownRefresh());
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    if(nextPageUrl) {
      this.getFootPrintList();
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },

  getFootPrintList: function(callback) {
    if(nextPageUrl) {
      util.get(nextPageUrl).then(res => {
        const data = res.data;
        console.log(data);
        nextPageUrl = data.next_page_url;
        this.setData({
          list: data.data
        });

        typeof callback === 'function' && callback();
      })
    }
     
  },

  navigatorToUrl: function(e){
    wx.navigateTo({
      url: e.currentTarget.dataset.url,
    })
  }
})