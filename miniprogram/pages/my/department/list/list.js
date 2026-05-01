const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        departments: [],
        current_page: 1,
        next_url: '',
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
        this.init();
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
    navigateToUrl(event) {
        let url = event.currentTarget.dataset.url;
        if (url && url !== '#') {
            wx.navigateTo({
                url: url
            });
        }
    },

    init: function () {
        this.setData({
            next_url: api.DepartmentListUrl,
            current_page: 1,
            departments: []
        });
        this.getDepartments();
    },

    getDepartments: function () {
        util.get(this.data.next_url).then(response => {
            let departments = response.data.data.departments;
            if(this.data.current_page === 1){
                this.data.departments = departments.data;
            }else{
                this.data.departments.concat(departments.data);
            }
            this.setData({
                current_page: departments.current_page,
                next_url: departments.next_page_url,
                departments: this.data.departments
            });
        })
    }
})