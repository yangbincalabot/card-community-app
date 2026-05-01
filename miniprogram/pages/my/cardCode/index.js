const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        userInfo: {},
        carte: {}, // 名片
        qrcode: '',
        hidden: true,
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
        this.getUserInfo();
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
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        // 分享时 exchange_type = 3
        return {
            title: this.data.carte.name + '的名片',
            path: 'pages/my/cardCode/cardCodeHandle/index?scene=user_id@' + this.data.userInfo.id + '|exchange_type@3'
        }
    },

    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            this.setData({
                userInfo: res.data.data
            });
            wx.hideLoading();
            // 判断是否已完善名片
            if (!this.data.userInfo.carte) {
                wx.showToast({
                    title: '请先完善名片信息',
                    icon: 'none',
                    duration: 2000
                });
                setTimeout(() => {
                    wx.redirectTo({
                        url: '../index/index'
                    });
                }, 2000);
                return;
            }

            if (!this.data.userInfo.qrcode){
                this.getQrcode();
            }else{
                this.setData({
                    qrcode: this.data.userInfo.qrcode
                });
            }

            this.setData({
                carte: this.data.userInfo.carte,
                hidden: false
            })
        });
    },
    getQrcode: function (event) {
        wx.showLoading({
            title: '生成中',
        });
        let param = {};
        if(event){
            let type = event.currentTarget.dataset.type;
            if(type && type === 'reset'){
                param.reset = true;
            }
        }
        util.post(api.QrcodeGetUrl, param).then(res => {
            wx.hideLoading();
            this.setData({
                qrcode: res.data
            });
        }).catch (err => {
            wx.hideLoading();
            console.log(err)
        })
    },

    downloadFile: function () {
        wx.downloadFile({
            url: this.data.qrcode,
            success: function (res) {
                wx.saveImageToPhotosAlbum({
                    filePath: res.tempFilePath,
                    success: function (data) {
                        wx.showToast({
                            title: '保存成功',
                            icon: 'success',
                            duration: 2000
                        })
                    }
                })
            }
        })
    }
})