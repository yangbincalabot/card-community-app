// pages/pay/offline_pay/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		orderId:'',
		totalAmount:0,
		bankInfo:{},
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		let that = this;
		let _orderId = options.id;
		if(_orderId){
			that.setData({
				orderId:_orderId
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
		that.getOrderDetailById();
	},

	// 根据订单id获取订单信息
	getOrderDetailById:function(){
		let that = this;
		let _id = that.data.orderId;
		let _requestUrl = api.OrderOfflineDetailUrl;
		let _formParams = {id:_id};
		util.post(_requestUrl,_formParams)
			.then(response => {
				let _responseData = response.data.data;
				that.setData({
					bankInfo:_responseData.bank_info,
					totalAmount:_responseData.order_info.total_amount
				});
			});
	},

	remindPaid:function(){
		let that = this;
		wx.showModal({
			title: '支付确认',
			content: '已完成线下转账支付？',
			success(res) {
				if (res.confirm) {
					that.confirmPaid();
				} else if (res.cancel) {

				}
			}
		})
	},

	confirmPaid:function(){
		let that = this;
		let _id = that.data.orderId;
		let _requestUrl = api.OrderOfflineRemindUrl;
		let _formParams = {id:_id};
		util.post(_requestUrl,_formParams)
			.then(response => {
				wx.navigateTo({
					url: '/pages/pay/result/index?id='+_id
				});
			});
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


})