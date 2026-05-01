// pages/discover/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
    ResourceRootUrl: api.ResourceRootUrl,
    currentTab: 1,
    responseData:{},
    listsData: [],
    typeArr:[
      {id:0, title:'全类型'}
    ],
    time_typeArr: [
      { id: 0, title: '全时段' },
      { id: 1, title: '今天' },
      { id: 2, title: '明天' },
      { id: 3, title: '后天' },
      { id: 4, title: '本周' },
      { id: 5, title: '本周末' }
    ],
    price_typeArr: [
      { id: 0, title: '价格' },
      { id: 1, title: '付费' },
      { id: 2, title: '免费' }
    ],
    searchValue:'',
    type:0,
    time_type:0,
    price_type:0,
    default_type_title:'全类型',
    default_time_title: '全时段',
    default_price_title: '价格'
	},

  clickTab: function (e) {
    let that = this;
    let _current = e.currentTarget.dataset.current;
    that.setData({
      currentTab: _current,
    })
  },

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
    let that = this;
    that.getActivityAllList();
    that.getType();
	},

  getType: function () {
    let that = this;
    util.post(api.ActivityGetType, {})
      .then(response => {
        let _data = response.data.data;
        let _oldData = that.data.typeArr;
        let _newData = _oldData.concat(_data);
        that.setData({
          typeArr: _newData
        })
      });
  },

  getActivityAllList: function (requestUrl) {
    let that = this;
    let _requestUrl = api.ActivityAllList;
    if (requestUrl != null) {
      _requestUrl = requestUrl;
    }
    let postParams = {
      searchValue: that.data.searchValue,
      type: that.data.type,
      time_type: that.data.time_type,
      price_type: that.data.price_type
    };
    util.post(_requestUrl, postParams)
      .then(response => {
        let _responseData = response.data;
        let _listsData = [];
        if (_responseData.data && _responseData.data.length>0) {
          _listsData = _responseData.data;
        }
        let _oldListData = that.data.listsData;
        let _newListData = _oldListData.concat(_listsData);
        console.log(_newListData);
        that.setData({
          responseData: _responseData,
          listsData: _newListData
        });
      });
  },

  changeKey: function (e) {
    let that = this;
    let _value = e.detail.value;
    console.log(_value);
    that.setData({
      searchValue: _value
    })
  },

  bindTypeChange: function (e) {
    let that = this;
    let key = e.detail.value;
    let currentType = that.data.typeArr[key];
    console.log(currentType)
    that.setData({
      'type': currentType.id,
      'default_type_title': currentType.title
    })
    that.realSearch();
  },


  bindTimeChange: function (e) {
    let that = this;
    let key = e.detail.value;
    let currentType = that.data.time_typeArr[key];
    console.log(currentType)
    that.setData({
      'time_type': currentType.id,
      'default_time_title': currentType.title
    })
    that.realSearch();
  },

  bindPriceChange: function (e) {
    let that = this;
    let key = e.detail.value;
    let currentType = that.data.price_typeArr[key];
    console.log(currentType)
    that.setData({
      'price_type': currentType.id,
      'default_price_title': currentType.title
    })
    that.realSearch();
  },

  realSearch: function () {
    let that = this;
    that.setData({
      listsData:[]
    })
    that.getActivityAllList();
  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
    })
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
    let that = this;
    that.setData({
      listsData:[]
    })
    that.getActivityAllList();
    setTimeout(function () {
      wx.stopPullDownRefresh();
    }, 1000);
	},

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function () {
    let that = this;
    let _nextUrl = that.data.responseData.next_page_url;
    if (_nextUrl) {
      that.getActivityAllList(_nextUrl);
    } else {
      console.log('没有内容了'); return;
    }
	},

})