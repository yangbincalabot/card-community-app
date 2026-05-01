// pages/my/apply/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        list: [],
        main_hidden: true,
        none_hidden: false,
        isHidden: true,
        promptParam:{},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        that.getList();
    },

    getList() {
        let that = this;
        util.post(api.ApplyGetMyList, {})
            .then(response => {
                let _data = response.data.data;
                let _new_data = [];
                let _none_hidden = that.data.none_hidden;
                console.log(_data)
                if (_data && _data.length > 0) {
                    _new_data = _data;
                    _none_hidden = true;
                }
                that.setData({
                    list: _new_data,
                    none_hidden: _none_hidden,
                    main_hidden: false
                })
            });
    },

    playPhone: function (e) {
        let that = this;
        let _phone = e.currentTarget.dataset.phone;
        if (!_phone) {
            wx.showToast({title: '主办方暂未设置电话', icon: 'none', duration: 800});
            return false;
        }
        wx.makePhoneCall({
            phoneNumber: _phone
        })
    },

    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        if (_url) {
            wx.navigateTo({
                url: _url
            })
        }
    },

    applyRefund:function(e){
        let that = this;
        let _id = e.currentTarget.dataset.id;
        that.showModal(_id);
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



    showModal(_id) {
        let that = this;
        that.setData({
            isHidden: false,
            currentId:_id
        });

    },

    /**
     * 隐藏模态框
     */
    hideModal: function () {
        let that = this;
        that.setData({
            isHidden: true,
            promptParam: {}
        })
    },

    inputChange: function (e) {
        let that = this;
        let _contentStr = 'promptParam.reason';
        that.setData({
            [_contentStr]: e.detail.value
        })
    },

    _cancelEvent() { //触发取消回调
        let that = this;
        that.setData({
            isHidden: true,
            promptParam: {}
        })
    },

    _confirmEvent() { //触发成功回调
        let that = this;
        let _id = that.data.currentId;
        let _reason = that.data.promptParam.reason;
        if (!_reason) {
            wx.showToast({
                title: '原因不能为空',
                icon: 'none',
                duration: 1000
            });
            return false;
        }

        let _postParams = {id:_id,reason:_reason};
        util.post(api.ApplyRefundUrl,_postParams)
            .then(response => {
                let _responseData = response.data.data;
                wx.showToast({
                    title: '申请成功',
                    icon: 'success',
                    duration: 2000
                });
                that.hideModal();
                that.onShow();
            });
    },
})