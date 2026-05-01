// pages/index/building/test/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		ResourceRootUrl:api.ResourceRootUrl,

		// 筛选
		isShow: true,
		currentTab: '',
		isHidden:true,

		searchTabData:[],
		searchParams:{},
		buildings:{},
		defaultSearchParam:{}
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		let that = this;
		let _currentSelected = options.current || '';
		that.setDefaultSearchData(options);
		util.post(api.buildingScreenConfigUrl,{}).then(respond =>{
			let respondData = respond.data.data;
			console.log(respondData);
			that.setData({
				searchTabData:respondData,
				currentTab:_currentSelected
			});
			that.setThreeLevelColumnDefault();
			that.setTwoLevelColumnDefault();
			that.setDefaultSelected(_currentSelected);
			console.log('that.data的内容',that.data);
		});
	},

	// 设置默认查询数据
	setDefaultSearchData(options){
		let that = this;
		console.log('options',options);
		that.setData({
			defaultSearchParam:options,
			searchKey:options.search_key || ''
		});
	},

	confirmSearch:function(){
		let that = this;
		console.log(that.data);
		// 处理一列筛选组数据
		that.setOneLevelSearchData();
		// 处理二列筛选组数据
		that.setTwoLevelSearchData();
		// 处理三列筛选组数据
		that.setThreeLevelSearchData();
		// 处理多选筛选组数据
		that.setMoreLevelSearchData();
		// 处理搜索框数据
		that.setInoutSearchData();

		let _postParams = that.data.searchParams;
		that.getSearchResult(_postParams);
		that.setData({
			isHidden: true,
		});
		console.log('confirmSearch');
	},

	getSearchResult: function (options) {
		let that = this;
		let postParams = {};
		if(options != undefined){
			postParams = options;
		}
		util.post(api.HousingAndBuildingSearchUrl,postParams)
			.then(response => {
				let _result = response.data.data;
				console.log(_result);
				that.setData({
					buildings:_result
				});
			});
	},

	setOneLevelSearchData:function(){
		let that = this;
		// 处理一列筛选组数据
		let _oneLevelData = that.data.OneLevelData;
		for (let _oneLevelDataKey in _oneLevelData){
			that.setSearchData('searchParams.'+_oneLevelDataKey,_oneLevelData[_oneLevelDataKey].currentOneLevelColumnId);
		}
	},

	setTwoLevelSearchData:function(){
		let that = this;
		// 处理一列筛选组数据
		let _twoLevelData = that.data.TwoLevelData;
		for (let _twoLevelDataKey in _twoLevelData){
			let _firstId = _twoLevelData[_twoLevelDataKey].currentTwoLevelColumnId;
			let _secondId = _twoLevelData[_twoLevelDataKey].currentTwoLevelSecondColumnId;
			if(_secondId){
				that.setSearchData('searchParams.'+_twoLevelDataKey+'.first_id',_firstId);
				that.setSearchData('searchParams.'+_twoLevelDataKey+'.second_id',_secondId);
			}
		}
	},

	setThreeLevelSearchData:function(){
		let that = this;
		// 处理一列筛选组数据
		let _threeLevelData = that.data.ThreeLevelData;
		for (let _threeLevelDataKey in _threeLevelData){
			let _firstId = _threeLevelData[_threeLevelDataKey].currentThreeLevelColumnId;
			let _secondId = _threeLevelData[_threeLevelDataKey].currentThreeLevelSecondColumnId;
			let _thirdId = _threeLevelData[_threeLevelDataKey].currentThreeLevelThirdColumnId;
			if(_secondId){
				that.setSearchData('searchParams.'+_threeLevelDataKey+'.first_id',_firstId);
				that.setSearchData('searchParams.'+_threeLevelDataKey+'.second_id',_secondId);
				that.setSearchData('searchParams.'+_threeLevelDataKey+'.third_id',_thirdId);
			}
		}
	},

	setMoreLevelSearchData:function(){
		let that = this;
		// 处理一列筛选组数据
		let _MoreSearchData = that.data.MoreSearchData;
		for (let _moreSearchDataKey in _MoreSearchData){
			let _id = _MoreSearchData[_moreSearchDataKey].currentMoreId;
			if(_id){
				that.setSearchData('searchParams.'+_moreSearchDataKey,_id);
			}
		}
	},

	setInoutSearchData:function(){
		let that = this;
		// 处理一列筛选组数据
		let _searchKey = that.data.searchKey;
		that.setSearchData('searchParams.search_key',_searchKey);
	},


	// 一级列筛选第一级点击事件
	oneLevelSelectedFirst:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;
		let _searchTabData = that.data.searchTabData;
		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'one_level'){
				let _currentOneLevelData = _searchTabData[_searchTabDataKey];
				let _currentOneLevelType = _currentOneLevelData['type'];
				if(_currentOneLevelType == _leveltype){
					// 设置一级选中的 id
					that.setSearchData('OneLevelData.'+_currentOneLevelType + '.currentOneLevelColumnId',_id);
					// 设置
					that.setSearchData('searchParams.'+_currentOneLevelType + '.id',_id);
					continue;
				}
				continue;
			}
		}

		that.confirmSearch();
	},

	setTwoLevelColumnDefault:function(){
		let that = this;
		let _searchTabData = that.data.searchTabData;
		// 获取所有二级筛选
		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'two_level'){
				let _currentTwoLevelData = _searchTabData[_searchTabDataKey];
				let _currentTwoLevelType = _currentTwoLevelData['type'];
				let _currentTwoLevelFirstData = _currentTwoLevelData['child'];

				let _currentTwoLevelSecondData = _currentTwoLevelFirstData[0]['child'];
				let _firstId = _currentTwoLevelFirstData[0]['child'][0]['id'];

				that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.currentTwoLevelColumnId',_firstId);
				that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.TwoLevelColumnFirstData',_currentTwoLevelFirstData);
				that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.TwoLevelColumnSecondData',_currentTwoLevelSecondData);
			}
		}
	},

	// 两级列筛选第一级点击事件
	twoLevelSelectedFirst:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;
		let _searchTabData = that.data.searchTabData;
		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'two_level'){
				let _currentTwoLevelData = _searchTabData[_searchTabDataKey];
				let _currentTwoLevelType = _currentTwoLevelData['type'];
				if(_currentTwoLevelType == _leveltype){
					// 获取一级数据
					let _currentTwoLevelFirstData = _currentTwoLevelData['child'];
					let _key = util.getObjKeyById(_currentTwoLevelFirstData,_id);

					if(_id > 0){
						// 获取二级数据
						let _currentTwoLevelSecondData = _currentTwoLevelFirstData[_key]['child'];
						that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.TwoLevelColumnSecondData',_currentTwoLevelSecondData);
					}

					// 设置一级选中的 id
					that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.currentTwoLevelColumnId',_id);

					// 取消上一次操作设置的二级 id
					that.setSearchData('TwoLevelData.'+_currentTwoLevelType + '.currentTwoLevelSecondColumnId',0);

					return false;
				}
				return false;
			}
		}
	},

	// 两列筛选第二级点击事件
	twoLevelSelectedSecond:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;
		// 设置第三级选中的id
		that.setSearchData('TwoLevelData.'+_leveltype + '.currentTwoLevelSecondColumnId',_id);

		let _twoLevelData = that.data.TwoLevelData;
		let _currentTwoLevelDataParams = _twoLevelData[_leveltype];
		console.log(_currentTwoLevelDataParams)
		that.confirmSearch();
	},


	setThreeLevelColumnDefault:function(){
		let that = this;
		let _searchTabData = that.data.searchTabData;
		// 获取所有三级筛选
		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'three_level'){
				let _currentThreeLevelData = _searchTabData[_searchTabDataKey];
				let _currentThreeLevelType = _currentThreeLevelData['type'];
				let _currentThreeLevelFirstData = _currentThreeLevelData['child'];
				let _currentThreeLevelSecondData = _currentThreeLevelFirstData[0]['child']['first'];
				let _currentThreeLevelThirdData = _currentThreeLevelFirstData[0]['child']['second'][_currentThreeLevelSecondData[0]['id']];

				let _firstId = _currentThreeLevelFirstData[0]['child']['first'][0]['id'];

				that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.currentThreeLevelColumnId',_firstId);
				that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnFirstData',_currentThreeLevelFirstData);
				that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnSecondData',_currentThreeLevelSecondData);
				that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnThirdData',{});
			}
		}
	},

	// 设置 data 的内容
	setSearchData:function(_name,_value){
		let that = this;
		that.setData({
			[_name]:_value,
		});
	},

	// 三列筛选第一级点击事件
	threeLevelSelectedFirst:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;

		let _searchTabData = that.data.searchTabData;

		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'three_level'){
				let _currentThreeLevelData = _searchTabData[_searchTabDataKey];
				let _currentThreeLevelType = _currentThreeLevelData['type'];
				if(_currentThreeLevelType == _leveltype){
					// 获取一级数据
					let _currentThreeLevelFirstData = _currentThreeLevelData['child'];
					let _key = util.getObjKeyById(_currentThreeLevelFirstData,_id);
					// 获取二级数据
					let _currentThreeLevelSecondData = _currentThreeLevelFirstData[_key]['child']['first'];
					// 获取三级数据
					let _currentThreeLevelThirdData = _currentThreeLevelFirstData[_key]['child']['second'][_currentThreeLevelSecondData[0]['id']];

					// 设置一级选中的 id
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.currentThreeLevelColumnId',_id);
					// 取消二级和三级之前选中的 id
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.currentThreeLevelSecondColumnId','');
					that.setSearchData('ThreeLevelData.'+_leveltype + '.currentThreeLevelThirdColumnId','');

					// 设置二级数据
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnSecondData',_currentThreeLevelSecondData);
					// 设置三级数据
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnThirdData',{});
					return false;
				}
				return false;
			}
		}

		console.log(_leveltype);
		console.log(_id);
	},

	// 三列筛选第二级点击事件
	threeLevelSelectedSecond:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;
		let _currentThreeLevelData = that.data.ThreeLevelData;
		let _firstId = _currentThreeLevelData[_leveltype].currentThreeLevelColumnId;
		let _searchTabData = that.data.searchTabData;

		for (let _searchTabDataKey in _searchTabData) {
			if(_searchTabData[_searchTabDataKey]['tab_type'] == 'three_level'){
				let _currentThreeLevelData = _searchTabData[_searchTabDataKey];
				let _currentThreeLevelType = _currentThreeLevelData['type'];
				if(_currentThreeLevelType == _leveltype){

					// 获取一级数据
					let _currentThreeLevelFirstData = _currentThreeLevelData['child'];
					let _key = util.getObjKeyById(_currentThreeLevelFirstData,_firstId);
					// 获取二级数据
					let _currentThreeLevelSecondData = _currentThreeLevelFirstData[_key]['child']['first'];
					let _currentKey = util.getObjKeyById(_currentThreeLevelSecondData,_id);

					// 设置第二级选中的id
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.currentThreeLevelSecondColumnId',_id);
					that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnSecondData',_currentThreeLevelSecondData);

					if(_id > 0){
						// 获取三级数据
						let _currentThreeLevelThirdData = _currentThreeLevelFirstData[_key]['child']['second'][_currentThreeLevelSecondData[_currentKey]['id']];
						that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnThirdData',_currentThreeLevelThirdData);
					}else {
						that.setSearchData('ThreeLevelData.'+_currentThreeLevelType + '.ThreeLevelColumnThirdData',{});
					}
					return false;
				}
				return false;
			}
		}
	},

	// 三列筛选第三级点击事件
	threeLevelSelectedThird:function(e){
		let that = this;
		let _leveltype = e.currentTarget.dataset.leveltype;
		let _id = e.currentTarget.dataset.id;
		// 设置第三级选中的id
		that.setSearchData('ThreeLevelData.'+_leveltype + '.currentThreeLevelThirdColumnId',_id);

		let _threeLevelData = that.data.ThreeLevelData;
		let _currentThreeLevelDataParams = _threeLevelData[_leveltype];
		console.log(_currentThreeLevelDataParams)
		that.confirmSearch();
	},

	moreSelected:function(e){
		let that = this;
		let _id = e.currentTarget.dataset.id;
		let _type = e.currentTarget.dataset.type;

		let _currentTypeSelectedData = that.data.MoreSearchData;
		if(_currentTypeSelectedData){
			let _currentTypeSelectedTypeData = _currentTypeSelectedData[_type];
			if(_currentTypeSelectedTypeData){
				let _currentTypeSelectedDataId = _currentTypeSelectedData[_type].currentMoreId;
				if(_currentTypeSelectedDataId == _id){
					that.setSearchData('MoreSearchData.' +  _type + '.currentMoreId',0);
					that.setSearchData('searchParams.'+_type,0);
					return false;
				}
			}
		}
		that.setSearchData('MoreSearchData.' +  _type + '.currentMoreId',_id);
	},


	// 下拉切换
	hideNav: function () {
		this.setData({
			isHidden: true
		})
	},

	setDefaultSelected:function(_currentSelected){
		let that = this;
		let _hasSelected = that.data.currentTab;
		let _currentIsHidden = that.data.isHidden;
		let _isHidden = true;

		if(_currentSelected === _hasSelected){
			console.log('_currentSelected',_currentSelected);
			console.log('_hasSelected',_hasSelected);
			console.log('_currentIsHidden',_currentIsHidden);
			if(!_currentSelected) {
				_isHidden = true;
			}else{
				_isHidden = false;
			}
		}

		this.setData({
			currentTab: _currentSelected,
			isHidden: _isHidden,
			selected: true
		});
		return false;
	},
	// 区域
	tabNav: function (e) {
		let that = this;
		// 点击一级筛选框，检查当前是否选中，如果选中，就关闭筛选框，如果没有选中，就显示当前筛选项
		let _currentSelected = e.currentTarget.dataset.current;
		let _hasSelected = that.data.currentTab;
		let _currentIsHidden = that.data.isHidden;
		let _isHidden = false;

		console.log('_currentSelected',_currentSelected);
		console.log('_hasSelected',_hasSelected);

		if(_currentSelected == _hasSelected){
			if(!_currentIsHidden) {
				_isHidden = true;
			}
		}

		// 获取当前选中的筛选看类型：column：单列筛选框；columns：多列筛选框；more：更多类型筛选框
		let _currentSelectedType = that.data.searchTabData[_currentSelected].type;
		let _currentSelectedData = that.data.searchTabData[_currentSelected].type;
		switch (_currentSelectedType) {
			case "columns":
				that.setColumnsSelectedData();
				break;
		}
		this.setData({
			currentTab: _currentSelected,
			isHidden: _isHidden,
			selected: true
		});
		return false;
	},

	// 设置多列下拉框内容
	setColumnsSelectedData:function(){

	},



	oneLevelSelected: function (e) {
		let that = this;
		let _id = e.currentTarget.dataset.id;
		that.setData({
			currentOneLevelColumnId:_id
		});
	},


	// 下拉切换中的切换
	// 区域
	twoLevelSelected: function (e) {
		let that = this;
		let _id = e.currentTarget.dataset.id;
		that.setData({
			currentTwoLevelColumnId:_id
		});
	},

	twoLevelSecondSelected: function (e) {
		let that = this;
		let _id = e.currentTarget.dataset.id;
		that.setData({
			currentTwoLevelSecondColumnId:_id
		});
	},


	stopPageScroll: function (e) {
		let that = this;
		that.hideNav()
	},




	/**
	 * 生命周期函数--监听页面初次渲染完成
	 */
	onReady: function () {
		let that = this;
		let _postParams = that.data.defaultSearchParam;
		that.getSearchResult(_postParams);
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
		let _id = e.currentTarget.dataset.id;
		let _url = e.currentTarget.dataset.url;
		wx.navigateTo({
			url: _url+'?id='+_id
		})
	},

	setSearchInputValue:function(e){
		var that = this;
		if (e.detail.value.length > 0){
			let _searchValue = e.detail.value;
			that.setData({
				searchKey:_searchValue
			});
			return;
		}
		that.setData({
			searchKey:''
		});
	},
	
	resetMoreParams:function () {
		let that = this;
		that.setData({
			MoreSearchData:{}
		});
	}
})