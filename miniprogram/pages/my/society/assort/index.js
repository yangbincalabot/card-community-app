// pages/my/society/assort/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    keyword: '',
    noBigData: {},
    noList: [],
    aid: 0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let _id = options.id;
    let aid = options.aid;
    this.setData({
      id: _id,
      aid,
    })
    this.getRoleCompany(_id);
    this.getNoSelectdCompany();
  },

  roleSave: function() {
    let _intValue = this.data.intValue;
    let _id = this.data.id;
    let aid = this.data.aid;
    if (!_intValue) {
      wx.showToast({ title: '请输入协会角色名称', icon: 'none', duration: 1500 });
      return false;
    }
    util.post(api.RoleStoreUrl, { name: _intValue, id: _id, aid }).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '保存成功', duration: 1500 });
    });
  },

  inputValue: function (e) {
    this.setData({
      intValue: e.detail.value
    });
    console.log(this.data.intValue)
  },

  changeSearch: function (e) {
    this.setData({
      keyword: e.detail.value
    })
  },

  searchValue: function () {
    this.getNoSelectdCompany();
  },

  getRoleCompany: function (_id) {
      let aid = this.data.aid;
    util.get(api.RoleCompanyUrl, { id: _id,  aid}, false).then(res => {
      console.log(res.data.data);
      let _data = res.data.data;
      this.setData({
        roleInfo: _data.roleInfo,
        companyList: _data.list,
        intValue: _data.roleInfo.name
      });
    });
  },

  getNoSelectdCompany: function (_nextUrl) {
    let _keyword = this.data.keyword;
    let _url = api.RoleNoSelectdCompanyUrl;
    let _noList = [];
    let aid = this.data.aid;
    if (_nextUrl) {
      _url = _nextUrl;
      _noList = this.data.noList;
    }
      util.get(api.RoleNoSelectdCompanyUrl, { keyword: _keyword, aid }, false).then(res => {
      console.log(res.data);
      let _responseData = res.data;
      let _newList = [];
      if (_responseData.data && _responseData.data.length > 0) {
        _newList = _responseData.data;
      }
      let _newListData = _noList.concat(_newList);
      this.setData({
        noBigData: _responseData,
        noList: _newListData
      });
    });
  },

  addCompany: function(e) {
    wx.showLoading({
      title: '处理中',
    })
    let _id = e.currentTarget.dataset.cid;
    let _role_id = this.data.id;
    let aid = this.data.aid;
      util.post(api.RoleAddCompanyRoleUrl, { id: _id, role_id: _role_id, aid }, false).then(res => {
      console.log(res.data.data);
      this.getRoleCompany(_role_id);
      this.getNoSelectdCompany();
      wx.hideLoading();
    });
    setTimeout(() =>{
      wx.hideLoading();
    }, 2000);
  },

  delCompany: function (e) {
    wx.showLoading({
      title: '处理中',
    })
    let _id = e.currentTarget.dataset.cid;
    let index = e.currentTarget.dataset.index;
    let _role_id = this.data.id;
    let aid = this.data.aid;
    let pivot = this.data.companyList[index].pivot;
    util.post(api.RoleDelCompanyRoleUrl, { id: _id, aid, pivot }, false).then(res => {
      console.log(res.data.data);
      this.getRoleCompany(_role_id);
      this.getNoSelectdCompany();
      wx.hideLoading();
    });
    setTimeout(() => {
      wx.hideLoading();
    }, 2000);
  },

  moveUp: function (e) {
    let id = e.currentTarget.dataset.id;
    let to_id = e.currentTarget.dataset.to_id;
    let index = e.currentTarget.dataset.index;
    console.log(e)
    if (!id || !to_id) {
      wx.showToast({ title: '数据错误，移动失败', icon: 'none', duration: 1500 });
      return false;
    }
    let provi = this.data.companyList[index].pivot;
    let to_provi = this.data.companyList[index - 1].pivot;
    let _post = {};
    _post.role_id = this.data.id;
    _post.id = id;
    _post.to_id = to_id;
    _post.type = 1;
    _post.aid = this.data.aid;
    _post.provi = provi;
    _post.to_provi = to_provi;
    util.post(api.RoleCompanyAdjustSortUrl, _post).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '操作成功', duration: 1500 });
      setTimeout(() => {
        this.getRoleCompany(this.data.id);
      }, 500);
    });

  },


  topUp: function (e) {
    let id = e.currentTarget.dataset.id;
    let index = e.currentTarget.dataset.index;
    if (!id) {
      wx.showToast({ title: '数据错误，置顶失败', icon: 'none', duration: 1500 });
      return false;
    }
    let company = this.data.companyList[index];
    let _post = {};
    _post.role_id = this.data.id;
    _post.id = id;
    _post.type = 2;
    _post.aid = this.data.aid;
    _post.pivot = company.pivot;
  
    util.post(api.RoleCompanyAdjustSortUrl, _post).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '操作成功', duration: 1500 });
      setTimeout(() => {
        this.getRoleCompany(this.data.id);
      }, 500);
    });
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
    let _nextUrl = this.data.noBigData.next_page_url;
    if (_nextUrl) {
      this.getNoSelectdCompany(_nextUrl);
    } else {
      console.log('没有内容了'); return;
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})