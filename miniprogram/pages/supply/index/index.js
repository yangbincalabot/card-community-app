// pages/supply/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        list:[],
        bigData: [],
        typeArr: [],
        options:false,
        type: 0,
        type_title: '全部',
        likeStatus: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        that.getType();
    },

    all:function(){
        this.setData({
            options: !this.data.options
        })
    },

    getList: function (_nextUrl) {
        let that = this;
        let _url = api.SupplyList;
        if (_nextUrl) {
            _url = _nextUrl;
        }
        let params = {};
        params.type = that.data.type;
        params.search = that.data.search;
        util.post(_url, params)
            .then(response => {
                let _bigData = response.data;
                let _data = response.data.data;
                console.log(_data);
                let _list = that.data.list;
                if (_data && _data.length>0) {
                    _list = _list.concat(_data);
                }
                that.setData({
                    list: _list,
                    bigData: _bigData
                })
            });
    },

    getType: function () {
        let that = this;
        util.post(api.SupplyType, {})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                that.setData({
                    typeArr: _data
                })
            });
    },

    changeSearch: function (e) {
        let that = this;
        let _value = e.detail.value;
        that.setData({
           search: _value
        });
    },

    searchBtn: function (e) {
        let that = this;
        that.setData({
            list:[],
            bigData: [],
        });
        that.getList();
    },

    changeType: function (e) {
        let that = this;
        let _typeArr = that.data.typeArr;
        let _type = e.currentTarget.dataset.type;
        let _index = e.currentTarget.dataset.index;
        let _type_title = '全部';
        if (_type != 0) {
            _type_title = _typeArr[_index].title;
        }
        that.setData({
            type: _type,
            type_title: _type_title,
            options: false,
            list:[],
            bigData: [],
        });
        that.getList();
    },

    playPhone: function (e) {
        let that = this;
        let _phone = e.currentTarget.dataset.phone;
        if (!_phone) {
            wx.showToast({title: '该用户未设置设置电话', icon: 'none', duration: 800});
            return false;
        }
        wx.makePhoneCall({
            phoneNumber: _phone
        })
    },

    changeLike: function (e) {
        let that = this;
        let _info_id = e.currentTarget.dataset.id;
      let _index = e.currentTarget.dataset.index;
        let _list = that.data.list;
        let _currentData = _list[_index];
        // 避免先执行下面导致报错
        setTimeout(() => {
            let _likes = _currentData.likes;
            let _title = '点赞成功';
            if (_currentData.likeStatus) {
                _title = '已取消点赞';
                _likes--;
            } else {
                _likes++;
            }
            util.post(api.LikeUrl, {type: 1, info_id: _info_id})
                .then(response => {
                    wx.showToast({title: _title, icon: 'none', duration: 800});
                    _currentData.likeStatus = !_currentData.likeStatus;
                    _currentData.likes = _likes;
                    _list[_index] = _currentData;
                    that.setData({
                        list: _list
                    })
                });
        },200);
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
      let that = this;
      that.setData({
        list: [],
        bigData: [],
      });
      that.getList();
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
            list:[],
            bigData: [],
        });
        that.getList();
        setTimeout(function () {
            wx.stopPullDownRefresh();
        }, 1000);
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        let that = this;
        let _nextUrl = that.data.bigData.next_page_url;
        if (_nextUrl) {
            that.getList(_nextUrl, {});
        } else {
            console.log('没有内容了'); return false;
        }
    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    }
});