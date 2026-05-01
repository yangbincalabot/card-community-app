const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        currentTab: 0, // 0-公司介绍， 1-供需
        hidden: true,
        userInfo: {},
        association: {},
        carte: {},
        logo_url: '',
        companySupplies: [], // 公司下的所有供需
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接,
        likeStatus: false,
        aid: 0,
        isJoined: false,
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let aid = options.id;
        if (!aid) {
            wx.showToast({
                title: '页面不存在',
                icon: 'none',
                mask: true
            });
            setTimeout(() => {
                wx.navigateBack();
            }, 2000)
        }
        let _currentTab = 0;
        if (options.tab) {
            _currentTab = 1;
        }
        this.setData({
            aid: aid,
            currentTab: _currentTab
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
    onShow: function (options) {
        wx.showLoading({
            title: '加载中',
            mask: true
        });
        this.getCompanyInfo();
        this.checkJoined();
    },

    /*
     * 复制到剪切板
     */
    clipboard: function (e) {
        util.getClipboard(e);
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
        this.getCompanyInfo();
        setTimeout(function () {
            wx.stopPullDownRefresh();
        }, 1000);
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if (parseInt(this.data.currentTab) === 1) {
            let current_page = this.data.current_page + 1;
            this.setData({
                current_page: current_page
            });
            if (this.data.current_page > this.data.last_page) {
                return;
            }
            this.getCompanySupply();
        }
    },
    navigatorToUrl: function (e) {
        let that = this;
        let _url = e.currentTarget.dataset.url;
        wx.navigateTo({
            url: _url
        })
    },
    // tab切换卡
    clickTab: function (e) {
        var that = this;
        if (this.data.currentTab === e.target.dataset.current) {
            return false;
        } else {
            that.setData({
                currentTab: e.target.dataset.current,
            })
        }
    },

    getCompanyInfo: function () {
        util.get(api.GetSocietyDetailUrl, { aid: this.data.aid }).then(res => {
            wx.hideLoading();
            let response = res.data.data;
            let company_card = response;
            let logo_url = response.image;
            let userInfo = response.user;
            this.setData({
                userInfo: userInfo,
                association: company_card,
                //carte: userInfo.carte,
                logo_url: logo_url,
            });

        });
    },

    getCompanySupply: function () {
        util.get(this.data.next_page_url, { aid: this.data.aid }).then((res => {
            let companySupplies = [];
            if (this.data.current_page > 1) {
                companySupplies = this.data.companySupplies.concat(res.data.data)
            } else {
                companySupplies = res.data.data;
            }
            this.setData({
                companySupplies: companySupplies,
                next_page_url: res.data.next_page_url ? res.data.next_page_url : api.getCompanyDetailSuppliesUrl,
                last_page: res.data.last_page ? res.data.last_page : 1
            })
        }))
    },

    // 联系他
    onCall: function (event) {
        let index = event.currentTarget.dataset.index;
        let phone = '';
        // 供需列表下的联系
        if (index !== undefined) {
            let carte = this.data.companySupplies[index].carte;
            if (carte && carte.phone) {
                console.log(carte.phone);
                phone = carte.phone;
            }
        } else {
            // 联系企业
            phone = this.data.association.contact_number;
            if (!phone) {
                let userInfo = this.data.userInfo;
                phone = (userInfo.carte && userInfo.carte.phone) ? userInfo.carte.phone : userInfo.phone;
            }
        }

        if (phone) {
            wx.makePhoneCall({
                phoneNumber: phone
            })
        } else {
            wx.showToast({
                title: '对方未填写电话',
                icon: 'none',
                duration: 2000
            })
        }

    },


    onShareAppMessage: function () {
        let association = this.data.association;
        let company_name = (association && association.company_name) ? association.company_name : this.data.userInfo.nickname + '的企业';
        if (association.images[0]) {
            return {
                title: company_name,
                path: 'pages/card/companyDetail/index?id=' + this.data.aid,
                imageUrl: association.images[0]
            }
        }
        return {
            title: company_name,
            path: 'pages/card/companyDetail/index?id=' + this.data.aid
        }
    },


    addSociety: function(){
        let aid = this.data.association.id;
        // 传递协会信息
        wx.navigateTo({
            url: '../societyApplication/societyApplication',
            success: res => {
                res.eventChannel.emit('application', this.data.association)
            }
        })

        // util.post(api.ApplicationSocietyUrl, {aid}).then(res => {
        //     wx.showToast({
        //         title: '等待审核',
        //     })
        // });
    },

    checkJoined: function () {
        let aid = this.data.aid;
        util.post(api.ApplicationSocietyCheckUrl, { aid }, false).then(res => {
            let isJoined = res.data.data.isJoined;
            let hidden = false;
            this.setData({ isJoined, hidden });
        });
    }

})