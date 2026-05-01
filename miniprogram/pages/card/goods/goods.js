const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        focus: false,
        inputValue: '',
        indicatorDots: false,
        autoplay: false,
        interval: 5000,
        duration: 1000,


        goods: [],
        cid: 0,
        page: 1,
        next_url: '',
        keywords: '',
    },

    bindButtonTap: function () {
        this.setData({
            focus: true
        })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let cid = parseInt(options.cid);
        if(cid <= 0){
            wx.navigateBack();
        }
        this.data.next_url = api.GoodsListUrl;
        this.data.cid = cid;
        this.getGoods()
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
        this.data.page = 1;
        this.data.next_url = api.GoodsListUrl;
        this.getGoods(() => {
            wx.stopPullDownRefresh();
        })
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if(!this.data.next_url){
            return;
        }
        this.getGoods();
    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },

    getGoods: function(callback){
        let { cid, keywords} = this.data
        util.get(this.data.next_url, {cid, keywords}).then(res => {
            let goodsData = res.data.data.goods;
            let goods;
            if(this.data.page === 1){
                goods = goodsData.data;
            }else{
                goods = this.data.goods.concat(goodsData.data);
            }
            this.setData({
                goods,
                next_url: goodsData.next_page_url,
                page: goodsData.current_page + 1
            }, () => {
                if(callback && typeof callback === 'function'){
                    callback();
                }
            })
        })
    },
    onSearch: function(){
        this.data.page = 1;
        this.data.next_url = api.GoodsListUrl;
        this.data.goods = [];
        this.getGoods();
    },

    changeKeyword: function(event){
        let keywords = event.detail.value;
        this.data.keywords = keywords;
    }
})