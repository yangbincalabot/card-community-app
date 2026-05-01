// pages/product/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		focus: false,
		inputValue: '',
		indicatorDots: false,
		autoplay: true,
		interval: 5000,
		duration: 1000,

		responseData:{},
		listsData:[],
		filtersData:{type:'normal'},
		categoryData:{},
		bannersData:{},
		searchKey:''
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
		let that = this;
		that.setData({
			responseData:{},
			listsData:[],
			filtersData:{}
		});
		that.getProductList({type:'normal'});
	},

	getProductList:function(options,requestUrl){
		let that = this;
		let postParams = {};
		if(options != undefined){
			postParams = options;
		}
		let _requestUrl = api.ProductIndexUrl;
		if(requestUrl != null){
			_requestUrl = requestUrl;
		}

		util.post(_requestUrl,postParams)
			.then(response => {
				let _responseData = response.data.data;
				let _productsData = _responseData.products;
				let _filtersData = _responseData.filters;
				let _categoryData = _responseData.category;
				let _bannersData = _responseData.banners;

				let _listsData = _productsData.data;
				let _oldListData = that.data.listsData;
				let _newListData = _oldListData.concat(_listsData);
				that.setData({
					responseData:_responseData,
					listsData:_newListData,
					filtersData:_filtersData,
					categoryData:_categoryData,
					bannersData:_bannersData,
				});
			});
	},

	changeCategory:function(e){
		let that = this;
		console.log('选择商品类型',e);
		let _type = e.currentTarget.dataset.type;
		let _filtersDataStr = 'filtersData.category_id';
		if(_type){
			console.log('_type',_type);
			that.setData({
				[_filtersDataStr] : _type,
				responseData:{},
				listsData:[],
			});
			let _postParams = that.data.filtersData;
			that.getProductList(_postParams);
		}

	},

	changeOrder:function(e){
		let that = this;
		console.log('选择排序方式',e);
		let _type = e.currentTarget.dataset.type;
		let _filtersDataStr = 'filtersData.order';
		let _lastOrderType = that.data.filtersData.order;
		if(_type){
			console.log('_type',_type);
			if(_type == 'price_desc'){
				// 如果当前选中的是价格倒序，上次选中的排序方式也是价格倒序，那么需要将价格倒序改为价格正序
				if(_type == _lastOrderType){
					_type = 'price_asc';
				}
			}
			that.setData({
				[_filtersDataStr] : _type,
				responseData:{},
				listsData:[],
			});
			let _postParams = that.data.filtersData;
			that.getProductList(_postParams);
		}
	},

	setSearchInputValue:function(e){
		let that = this;
		console.log(e.detail.value.length);
		let _filtersDataStr = 'filtersData.search';
		if (e.detail.value.length > 0){
			console.log('大于0');
			let _searchValue = e.detail.value;
			that.setData({
				[_filtersDataStr]:_searchValue
			});
			return;
		}else{
			that.setData({
				[_filtersDataStr]:''
			});
		}
	},

	confirmSearch:function(){
		let that = this;
		console.log(that.data);
		that.setData({
			responseData:{},
			listsData:[],
		});
		let _postParams = that.data.filtersData;
		that.getProductList(_postParams);
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
		let that = this;
		let _nextUrl = that.data.responseData.products.next_page_url;
		let _filtersDataStr = that.data.filtersData;
		console.log('_filtersDataStr',_filtersDataStr);
		if(_nextUrl){
			that.getProductList(_filtersDataStr,_nextUrl);
		}else{
			console.log('没有内容了');return;
		}
	},



	navigatorToUrl:function (e) {
		let _url = e.currentTarget.dataset.url;
		if(_url){
			wx.navigateTo({
				url: _url
			})
		}
	},
})