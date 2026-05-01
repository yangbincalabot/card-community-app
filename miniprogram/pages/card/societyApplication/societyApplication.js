const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        application: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        const eventChannel = this.getOpenerEventChannel();
        eventChannel.on('application', application => {
            this.setData({ application})
        });

        setTimeout(() => {
            if(Object.keys(this.data.application).length === 0){
                wx.navigateBack()
            }
        }, 1200);

        this.getUserInfo();

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

    formSubmit: function(e){
        let reason = e.detail.value.reason;
        if(!reason){
            wx.showToast({
                title: '请输入理由',
                icon: 'none'
            });
            return;
        }


        let association = this.data.application;
        let userInfo = this.data.userInfo;

        if (association.user_id === userInfo.id){
            wx.showToast({
                title: '创建者不能申请',
                icon: 'none'
            })
            return;
        }

        if (!userInfo.companyCardStatus){
            wx.showToast({
                title: '请升级企业会员',
                icon: 'none'
            })
            return;
        }

        if(association.user_id === 0){
            wx.showToast({
                title: '非法操作',
                icon: 'none'
            })
            return;
        }



        // 免费直接跳转提示页面
        let fee = Number(association.fee);
        if(fee > 0){
            wx.navigateTo({
                url: '../societyPay/societyPay',
                success: res => {
                    res.eventChannel.emit('application', { association, reason })
                }
            })
        }else{
            this.postApplication(reason, () => {
                wx.redirectTo({
                    url: '../societyPaySuccess/societyPaySuccess'
                })
            })
        }
        
    },
    postApplication: function (reason, callback){
        let aid = this.data.application.id;
        util.post(api.ApplicationSocietyUrl, { aid, reason}).then(res => {
            if(callback && typeof callback === 'function'){
                callback();
            }else{
                wx.showToast({
                    title: '等待审核',
                })
            }
        });
    },

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            this.setData({
                userInfo: res.data.data
            });
        });
    },

    


})