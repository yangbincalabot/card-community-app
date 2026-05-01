const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        userInfo: {}, // 当前用户
        id: 0, // 当前id
        hidden: true,
        cardDetail: {},
        is_send: false, // 是否传递过名片,
        is_collect: false, // 是否收藏,
        phone: '',
        wechat: '',
        email: '',
        address_title: '',
        offlineBusinessCard: [], // 收到的名片
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
            id: id,
        });
        this.getUserInfo();
    },

    toBack: function () {
      wx.navigateBack({
        delta: 1
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
        this.getCardDetail();
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

    /**
     * 用户点击右上角分享，个人名片
     */
    onShareAppMessage: function () {
        let cardDetail = this.data.cardDetail;
        let title = (cardDetail && cardDetail.name) ? cardDetail.name : cardDetail.user.nickname;
        return {
            title: title + '的名片',
            path: 'pages/my/cardCode/cardCodeHandle/index?scene=user_id@' + cardDetail.uid
        }
    },
    prompt: function () {
        wx.showToast({ title: '页面不存在', icon: 'none', duration: 2000 });
        setTimeout(function () {
            wx.navigateBack({
                delta: 1
            })
        }, 2000);
    },

    getCardDetail: function(){
        util.get(api.getCardDetailUrl, {id: this.data.id}).then(res => {
            let response = res.data.data;
            console.log(response);
            this.setData({
                cardDetail: response.carte,
                offlineBusinessCard: response.offlineBusinessCard
            });
            // 检查名片收藏状态
            this.getCollectionStatus();

            // 检查名片公开状态
            //this.checkCardOpen();

            // 检查名片发送状态
            this.checkReceiveStatus();
        })
    },
    // 跳转页面
    navigatorToUrl: function (event) {
        let url = event.currentTarget.dataset.url;
        if(url){
            wx.navigateTo({
                url: url
            });
        }
    },
    // 传递名片
    sendCard: function(){
        let userInfo = this.data.userInfo;
        let cardDetail = this.data.cardDetail;
        if(userInfo && userInfo.id === cardDetail.uid){
            wx.showToast({title : "不能发给自己", icon : 'none', duration : 2000});
            return;
        }
        if(!cardDetail.id){
            wx.showToast({title : "非法操作", icon : 'none', duration : 2000});
            return;
        }
        util.post(api.SendCardUrl,{card_id: cardDetail.id}).then(res => {
            wx.showToast({title : "发送成功", icon : 'none', duration : 2000});
            this.getCollectionStatus();
            this.setData({
                is_send: true
            });
        });
    },

    getCollectionStatus: function () {
        let _user = this.data.userInfo;
        if (!_user) {
            return false;
        }
        let param = {};
        param.info_id = this.data.id;
        param.type = 1; // 名片类型
        util.post(api.CollectionGetStatusUrl, param)
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                let is_collect = false;
                if (_data && _data.status === 1) {
                    is_collect = true;
                }
                this.setData({
                    is_collect:is_collect
                })
            });
    },

    // 收藏
    clickCollection:function(){
        let _user = this.data.userInfo;
        // 不能收藏自己
        if(_user && _user.id === this.data.cardDetail.uid){
            wx.showToast({title : "不能收藏自己", icon : 'none', duration : 2000});
            return;
        }

        let param = {};
        param.info_id = this.data.cardDetail.id;
        param.type = 1;
        util.post(api.CollectionUrl, param)
            .then(response => {
                //  提示信息
                let is_collect = !this.data.is_collect;
                let msg = is_collect === true ? '收藏成功' : '取消成功';
                this.setData({
                    is_collect:is_collect
                });
                wx.showToast({
                    title: msg,
                    icon: 'none',
                    duration: 1500
                });
                //this.getCollectionStatus(true);
            });
    },

    // 检查公开状态
    checkCardOpen: function(){
        let cardDetail = this.data.cardDetail;
        let phone, wechat, email, address_title = '';
        if(cardDetail.open === 1 || (this.data.userInfo && this.data.userInfo.id === cardDetail.id)){
            // 公开
            phone = cardDetail.phone;
            wechat = cardDetail.wechat;
            email = cardDetail.email;
            address_title = cardDetail.address_title;
            this.setData({
                phone: phone,
                wechat: wechat,
                email: email,
                address_title:address_title,
            });
        }else{
            util.post(api.CardOpenDetail,{card_id: this.data.cardDetail.id}).then(res => {
                let response = res.data.data;
                this.setData({
                    phone: response.phone,
                    wechat: response.wechat,
                    email: response.email,
                    address_title: response.address_title,
                });
            });
        }
        wx.hideLoading();

    },

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            console.log(res.data.data);
            this.setData({
                userInfo: res.data.data
            });
        });
    },


    checkReceiveStatus: function () {
        util.post(api.CheckReceiveStatus,{user_id: this.data.cardDetail.uid}).then(res => {
            let response = res.data.data;
            this.setData({
                hidden: false,
                is_send: Boolean(response.status)
            })
        });
    },


})