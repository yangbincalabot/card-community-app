const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        currentTab: 0, // 0-公司介绍， 1-供需
        hidden: true,
        userInfo:{},
        companyCard: {},
        carte: {},
        logo_url: '',
        companySupplies: [], // 公司下的所有供需
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接,
        company_id: 0,
        likeStatus: false,
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let company_id = options.id;
        if(!company_id){
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
            company_id: company_id,
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
        this.setData({
            next_page_url: api.getCompanyDetailSuppliesUrl
        });
        this.getCompanySupply();
        this.getCompanyInfo();
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
        this.setData({
            next_page_url: api.getCompanyDetailSuppliesUrl,
            current_page: 1,

        });
        this.getCompanySupply();
        this.getCompanyInfo();
        setTimeout(function () {
          wx.stopPullDownRefresh();
        }, 1000);
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if(parseInt(this.data.currentTab) === 1){
            let current_page = this.data.current_page + 1;
            this.setData({
                current_page: current_page
            });
            if(this.data.current_page > this.data.last_page){
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
        util.get(api.GetCompanyDetailUrl, {company_id: this.data.company_id}).then(res => {
            wx.hideLoading();
            let response =  res.data.data;
            let company_card = response;
            let logo_url = response.logo;
            let userInfo = response.user;
            if(!logo_url){
                logo_url = userInfo.avatar;
            }
            this.setData({
                userInfo: userInfo,
                companyCard: company_card,
                carte: userInfo.carte,
                logo_url: logo_url,
                hidden: false
            });

        });
    },

    getCompanySupply: function() {
        util.get(this.data.next_page_url, {company_id: this.data.company_id}).then((res => {
            let companySupplies = [];
            if(this.data.current_page > 1){
                companySupplies = this.data.companySupplies.concat(res.data.data)
            }else{
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
        if(index !== undefined){
            let carte = this.data.companySupplies[index].carte;
            if(carte && carte.phone){
                console.log(carte.phone);
                phone = carte.phone;
            }
        }else{
            // 联系企业
            phone = this.data.companyCard.contact_number;
            if(!phone){
                let userInfo = this.data.userInfo;
                phone = (userInfo.carte && userInfo.carte.phone) ? userInfo.carte.phone : userInfo.phone;
            }
        }

        if(phone){
            wx.makePhoneCall({
                phoneNumber: phone
            })
        }else{
            wx.showToast({
                title: '对方未填写电话',
                icon: 'none',
                duration: 2000
            })
        }

    },

    // 点赞
    changeLike: function (e) {
        let _info_id = e.currentTarget.dataset.id; // 名片id
        let _index = e.currentTarget.dataset.index;
        let _list = this.data.companySupplies;
        let _currentData = _list[_index]; // 选中的供需
        let _likes = _currentData.likes;
        let _title = '点赞成功';
        if (_currentData.likeStatus) {
            _title = '已取消点赞';
            if(_likes > 0){
                _likes--;
            }
        } else {
            _likes++;
        }
        util.post(api.LikeUrl, {type: 1, info_id: _info_id})
            .then(response => {
                wx.showToast({title: _title, icon: 'none', duration: 800});
                _currentData.likeStatus = !_currentData.likeStatus;
                _currentData.likes = _likes;
                _list[_index] = _currentData;
                this.setData({
                    companySupplies: _list
                })
            });
    },

    onShareAppMessage: function () {
        let companyCard = this.data.companyCard;
        let company_name = (companyCard && companyCard.company_name) ? companyCard.company_name : this.data.userInfo.nickname +  '的企业';
        if(companyCard.images[0]){
            return {
                title: company_name,
                path: 'pages/card/companyDetail/index?id=' + this.data.company_id,
                imageUrl: companyCard.images[0]
            }
        }
        return {
            title: company_name,
            path: 'pages/card/companyDetail/index?id=' + this.data.company_id
        }
    },

})