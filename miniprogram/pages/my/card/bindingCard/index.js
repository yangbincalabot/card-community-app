const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        hidden: true,
        companies: [], // 公司
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接
        search: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let is_binding = wx.getStorageSync('BINDINGCARD');
        if(is_binding !== true){
            wx.redirectTo({
                url: '../../index/index'
            })
            return;
        }
        this.setData({
            hidden: false
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
        wx.showLoading({
            title: '加载中',
        });
        this.setData({
            next_page_url: api.GetCompanyListUrl
        });
        this.getCompanies();
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
        this.setData({
            next_page_url: api.CommunalListUrl,
            current_page: 1
        });
        this.getCompanies();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        let current_page = this.data.current_page + 1;
        this.setData({
            current_page: current_page
        });
        if(this.data.current_page > this.data.last_page){
            return;
        }
        this.getCompanies();
    },
    getCompanies: function() {
        util.get(this.data.next_page_url, this.data.search).then(res => {
            wx.hideLoading();
            let companies = [];
            if(this.data.current_page > 1){
                companies = this.data.companies.concat(res.data.data);
            }else{
                companies = res.data.data;
            }

            this.setData({
                companies: companies,
                next_page_url: res.data.next_page_url,
                last_page: res.data.last_page
            });
        })
    },

    toBind: function (event) {
        let index = event.target.dataset.index;
        if(index !== undefined){
            let company = this.data.companies[index];
            if(company){
                wx.setStorageSync('BIND_COMPANY_NAME', company.company_name); // 公司名
                wx.setStorageSync('BIND_COMPANY_ID', company.id); // 公司id
                wx.removeStorageSync('BINDINGCARD');
                // 返回上一页
                wx.navigateBack();
                return;
            }else{
                wx.showToast({
                    title: '公司不存在',
                    icon: 'none',
                    duration: 2000
                });
            }

        }else{
            wx.showToast({
                title: '点击异常',
                icon: 'none',
                duration: 2000
            });
        }
        setTimeout(() => {
            wx.hideToast();
        }, 2000)
    },

    changeSearch: function (e) {
        let _value = e.detail.value;
        this.setData({
            search: {company_name: _value}
        });
    },
    searchBtn: function(){
        this.setData({
            companies:[],
            next_page_url: api.GetCompanyListUrl,
            current_page: 1
        });
        this.getCompanies();
    }

})