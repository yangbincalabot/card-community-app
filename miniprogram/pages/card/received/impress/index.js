const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        id: 0,
        detail: {},
        from_user_carte: {},
        tag_title: '',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let id = options.id;
        if(!id){
            this.prompt();
            return;
        }
        this.setData({
            id: id
        });

        wx.showLoading({
            title: '加载中',
        });
        this.getDetail();
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
    prompt: function () {
        wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
        setTimeout(function () {
            wx.navigateBack({
                delta: 1
            })
        }, 500);
    },
    getDetail: function () {
        util.post(api.GetReceiveCardDetailUrl, {id: this.data.id}).then((res) => {
            let response = res.data.data;
            this.setData({
                detail: response,
                from_user_carte: response.from_user.carte,
                tag_title: (response.from_user && response.from_user.tag) ? response.from_user.tag.title : ''
            });
        });
    },
    tagContent: function (event) {
        this.data.tag_title = event.detail.value;
    },
    changeTag: function () {
        util.post(api.UpdateReceiveCarUrl, { id: this.data.id, tag_title: this.data.tag_title }).then((res) => {
            wx.showToast({
                title : "编辑标记成功" ,
            });
        });
    },
    // 输入标记
    inputTag: function(e){
        let value = e.detail.value;
        this.setData({
            tag_title: value
        })
    },
    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        if(_url){
            wx.navigateTo({
                url: _url
            })
        }
    }
})