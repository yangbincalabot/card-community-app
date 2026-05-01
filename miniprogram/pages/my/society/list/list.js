const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isHidden: true,
        associations: [],
        page: 1,
        next_page_url: api.BusinessAssociationIndexUrl
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
            next_page_url: api.BusinessAssociationIndexUrl
        })
        this.getData(() => {
            wx.stopPullDownRefresh();
        })
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if (!this.data.next_page_url) {
            return;
        }

        this.getData();
    },
    getData: function (callback) {
        util.get(this.data.next_page_url).then(res => {
            let associationsData = res.data.data.associations.data;
            let associations = this.data.associations;
            if (this.data.page === 1) {
                associations = associationsData;
            } else {

                associations = associations.concat(associationsData)
            }
            this.setData({
                isHidden: false,
                associations: associations,
                next_page_url: res.data.data.associations.next_page_url,
                page: res.data.data.associations.current_page
            })

            if (callback && typeof callback === 'function') {
                callback()
            }
        })
    },

    changeShow: function (event) {
        let id = event.currentTarget.dataset.id;
        let index = event.currentTarget.dataset.index;
        let is_show = !this.data.associations[index].is_show
        util.post(api.BusinessassociationsUpdateUrl, { id: id, is_show: is_show }).then(res => {
            wx.showToast({
                title: '编辑成功',
            })
            this.setData({
                [`associations[${index}].is_show`]: is_show
            })
        })
    },

    delete: function (event) {
        let id = event.currentTarget.dataset.id;
        let index = event.currentTarget.dataset.index;
        let is_show = !this.data.associations[index].is_show;
        wx.showModal({
            title: '提示',
            content: '确实删除?',
            success: res => {
                if (res.confirm === true) {
                    util.post(api.BusinessassociationsDeleteUrl, { id: id }).then(res => {
                        wx.showToast({
                            title: '删除成功',
                        })
                        this.data.associations.splice(index, 1)
                        this.setData({
                            associations: this.data.associations
                        })
                    })
                }
            }
        })

    },

    goDetail: function(e){
        let index = e.currentTarget.dataset.index;
        let association = this.data.associations[index];
        if (association.status !== 2){
            return ;
        }

        wx.navigateTo({
            url: '/pages/card/societyDetail/societyDetail?id=' + association.id,
        })
    }
})