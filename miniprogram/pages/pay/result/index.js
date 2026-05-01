// pages/pay/result/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		orderId:'',
		orderInfo:{}
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
		let _requestUrl = api.OrderSuccessPayDetailUrl;
		let _formParams = {id:_id};
		util.post(_requestUrl,_formParams)
			.then(response => {
				let _responseData = response.data.data;
				that.setData({
					orderInfo:_responseData
				});
			});
	},

	// 返回 返回到购物车
	backToCart:function(){
		wx.redirectTo({
			url: '/pages/cart/index/index'
		})
	},
	// 查看订单 跳到订单详情页
	viewOrderDetail:function(){
		let that = this;
		let _id = that.data.orderId;
		wx.navigateTo({
			url: '/pages/my/order/detail/index?id='+_id
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