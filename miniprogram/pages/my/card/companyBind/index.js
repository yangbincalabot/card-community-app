const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        list: [],
        bigData: [],
        joinlist: [],
        joinData: [],
        delBtnWidth: 180,
        active:false,


        companyBinds: [],
        url: '',
        currentPage: 1,
        carte_id: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {

    },

    getList: function () {
        util.get(api.getCompanyBindsUrl).then((response) => {
            let binds = response.data.data.companyBinds.data;
            let companyBinds = [];
            if(this.data.currentPage === 1){
                companyBinds = binds;
            }else{
                companyBinds = this.data.companyBinds.concat(binds)
            }
            this.setData({
                companyBinds: companyBinds,
                url: response.data.data.companyBinds.next_page_url,
                currentPage: response.data.data.companyBinds.current_page
            });
            wx.stopPullDownRefresh();

        });
    },


    realDelete: function (e) {
        let that = this;
        let _id = e.currentTarget.dataset.id;
        let _key = e.currentTarget.dataset.key;
        wx.showModal({
            title: '删除',
            content: '删除后不可撤销，您确定删除该条信息吗？',
            success(res) {
                if (res.confirm) {
                    util.post(api.ActivityDelete, {id:_id})
                        .then(response => {
                            let _list = that.data.list;
                            _list.splice(_key, 1);
                            that.setData({
                                list: _list
                            })
                            wx.showToast({ title: '删除成功', icon: 'none', duration: 800 });
                        });
                }
            }
        })

    },


    navigateToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        wx.navigateTo({
            url: _url
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
        this.getList();
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
        this.init();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if(!this.data.url){
            return ;
        }
        this.getList();
    },

    init: function() {
        this.setData({
            url: api.getCompanyBindsUrl,
            companyBinds: [],
            currentPage: 1
        });

        this.getList();
    },

    bindOperate: function(e){
        let type = e.currentTarget.dataset.type;
        let id = e.currentTarget.dataset.id;
        let carte_id = e.currentTarget.dataset.carte_id;
        this.setData({
            carte_id: carte_id
        });


        // 操作前判断用户是否同时绑定其它公司
        util.get(api.getUserLastCompanyBindUrl, {id: id}, false).then((response) => {
            let is_bind_other = response.data.data.status;
            if(is_bind_other === true){
                wx.showModal({
                    title: '提示',
                    content: '该用户已申请其它公司，是否继续操作？',
                    success: (res) => {
                        if (res.confirm) {
                            this.bindRequest(id, type, carte_id);
                        } else if (res.cancel) {
                            this.bindRequest(id, 'refuse', carte_id);
                        }
                    },
                    cancelText: '拒绝',
                    confirmText: '同意',
                });
            }else{
                this.bindRequest(id, type);
            }
        });
    },
    bindRequest: function(id, type){
        util.post(api.CompanyBindOperateUrl, {
            _method: 'PUT',
            type: type,
            id: id
        }).then((response) => {
            wx.showToast({
                title: '操作成功'
            });
            if(type === 'refuse'){
                setTimeout(() => {
                    this.getList();
                }, 1500)
            }else{
                setTimeout(() => {
                    wx.navigateTo({
                        url: '/pages/my/department/bind/index?carte_id=' + this.data.carte_id
                    })
                }, 1500)
            }

        })
    }
})