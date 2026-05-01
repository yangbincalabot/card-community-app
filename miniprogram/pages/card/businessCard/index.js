const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');

Page({

    /**
     * 页面的初始数据
     */
    data: {

    },
    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        wx.navigateTo({
            url: _url
        })
    },
    code:function(){
        wx.chooseImage({
            count: 1,
            sizeType: ['original', 'compressed'],
            sourceType: ['album', 'camera'],
            success: (res) => {
                wx.showLoading({
                    title: '解析中...',
                });
                let code_img = res.tempFilePaths[0];
                wx.uploadFile({
                    url: api.ScanCardSaveUrl,
                    filePath: code_img,
                    name: 'file',
                    header: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        Authorization: 'Bearer '+wx.getStorageSync('token')
                    },
                    success: (res) => {
                        wx.hideLoading();
                        console.log(res);
                        if(res.statusCode !== 200){
                            let data = JSON.parse(res.data);
                            wx.showToast({
                                title: data.message,
                                icon: 'none',
                                duration: 2000
                            });
                            return;
                        }
                        let data = JSON.parse(res.data);
                        wx.redirectTo({
                            url: '../../my/card/editCard/index'
                        })
                    },
                    fail: function(error){
                        wx.hideLoading();
                        console.log(error);
                        wx.showToast({
                            title: '解析失败',
                            icon: 'none',
                            duration: 2000
                        })
                    }
                })
            },
            fail: function(error){
                console.log(error);
            }
        })
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