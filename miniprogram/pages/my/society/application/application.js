const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        applications: [],
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接
        perfect_text: ['', '', '信息不全', '待完善'],
        aid: 0,
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let aid = options.aid;
        this.setData({
            aid
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
        wx.showLoading({
            title: '加载中',
        });
        this.setData({
            applications: [],
            next_page_url: api.AssociationsApplicationUrl
        });
        this.getApplications();

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

    init: function () {
        this.setData({
            next_page_url: api.AssociationsApplicationUrl,
            current_page: 1,
        });
        this.getApplications();
    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        this.init();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if (!this.data.next_page_url) {
            return;
        }
        this.getApplications();
    },

    getApplications: function () {
        let params = {}
        params.aid = this.data.aid;
        util.get(this.data.next_page_url, params).then((res) => {
            wx.hideLoading();
            let applications = [];
            console.log(res)
            let response = res.data.data.applications.data;

            if (this.data.current_page > 1) {
                applications = this.data.applications.concat(response);
            } else {
                applications = response;
            }




            this.setData({
                applications: applications,
                next_page_url: res.data.data.applications.next_page_url,
                current_page: res.data.data.applications.next_page_url,
            });

            wx.stopPullDownRefresh();
        })
    },


    verify: function(e){
        let id = e.currentTarget.dataset.id;
        let company = e.currentTarget.dataset.company;
        let reason = e.currentTarget.dataset.reason;
        wx.showModal({
            title: '审核操作',
            content: reason ? '申请理由:' + reason : '审核公司入会操作',
            cancelText: '拒绝',
            confirmText: '通过',
            success: res => {
                let status = 0;
                if(res.confirm){
                    status = 2;
                }else{
                    status = 1;
                }

                util.post(api.AssociationsVerifyUrl, {aid: this.data.aid, id, status}).then(res => {
                    wx.showToast({
                        title: '操作成功',
                    });

                    setTimeout(() => {
                        this.init();
                    }, 1200);
                })
            }
        })
    }

})