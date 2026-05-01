const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        goods: [],
        page: 1,
        next_page_url: api.BusinessGoodsIndexUrl
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.getData()
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
        this.setData({
            page: 1,
            next_page_url: api.BusinessGoodsIndexUrl
        })
        this.getData(() => {
            wx.stopPullDownRefresh();
        })
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if (!this.data.next_page_url){
            return;
        }

        this.getData();
    },
    getData: function(callback){
        util.get(this.data.next_page_url).then(res => {
            let goodsData = res.data.data.goods.data;
            let goods = this.data.goods;
            if(this.data.page === 1){
                goods = goodsData;
            }else{

                goods = goods.concat(goodsData)
            }
            this.setData({
                goods: goods,
                next_page_url: res.data.data.goods.next_page_url,
                page: res.data.data.goods.current_page
            })

            if(callback && typeof callback === 'function'){
                callback()
            }
        })
    },

    changeShow: function(event) {
        let id = event.currentTarget.dataset.id;
        let index = event.currentTarget.dataset.index;
        let is_show = !this.data.goods[index].is_show
        util.post(api.BusinessGoodsUpdateUrl, { id: id, is_show: is_show}).then(res => {
            wx.showToast({
                title: '编辑成功',
            })
            this.setData({
                [`goods[${index}].is_show`] : is_show
            })
        })
    },

    delete: function(event){
        let id = event.currentTarget.dataset.id;
        let index = event.currentTarget.dataset.index;
        let is_show = !this.data.goods[index].is_show;
        wx.showModal({
            title: '提示',
            content: '确实删除?',
            success: res => {
                if(res.confirm === true){
                    util.post(api.BusinessGoodsDeleteUrl, { id: id }).then(res => {
                        wx.showToast({
                            title: '删除成功',
                        })
                        this.data.goods.splice(index, 1)
                        this.setData({
                            goods: this.data.goods
                        })
                    })
                }
            }
        })
        
    }
})