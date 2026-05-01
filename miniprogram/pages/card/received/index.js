const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        from_users: [],
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        next_page_url: '', // 下一页链接
        perfect_text: ['', '','信息不全', '待完善'],
        type: 0,
        currentTab:0,
        within_three_days: [], // 三天内
        three_days_ago: [], // 三天前
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        if(options && options.type){
            this.setData({
                type: options.type
            })
        }
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
            from_users: [],
            within_three_days: [],
            three_days_ago: [],
            next_page_url: api.GetReceiveCardUrl
        });
        this.getReceiveCards();

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

    init : function() {
        this.setData({
            next_page_url: api.GetReceiveCardUrl,
            current_page: 1,
            within_three_days: [],
            three_days_ago: []
        });
        this.getReceiveCards();
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
        if(!this.data.next_page_url){
            return;
        }
        this.getReceiveCards();
    },

    getReceiveCards: function () {
        let params = {}
        // if(this.data.type && this.data.next_page_url.indexOf('type=') === -1){
        //     params.type = this.data.type;
        // }
        switch (parseInt(this.data.currentTab)) {
            case 0:
                params.type = 1;
                break;
            case 1:
                params.type = 3;
                break;
            case 2:
                params.type = 2;
                break;
        }
        util.get(this.data.next_page_url, params).then((res) => {
            wx.hideLoading();
            let from_users = [];
            let response = res.data.data;

            if(this.data.current_page > 1){
                from_users = this.data.from_users.concat(response);
            }else{
                from_users = response;
            }



            let within_three_days = this.data.within_three_days;
            let three_days_ago = this.data.three_days_ago;
            for(let [key, value] of from_users.entries()){
                let created_at = new Date(value.created_at);
                // value.time = created_at.getFullYear() + '年' + (created_at.getMonth() + 1) + '月' + created_at.getDate() + '日';
                value.time = (created_at.getMonth() + 1) + '月' + created_at.getDate() + '日';
                if(value.is_within === true){
                    // 3天内
                    within_three_days = within_three_days.concat(value);
                }else{
                    three_days_ago = three_days_ago.concat(value);
                }
            }


            this.setData({
                from_users: from_users,
                next_page_url: res.data.next_page_url,
                //last_page: res.data.last_page,
                current_page: res.data.current_page,
                within_three_days: within_three_days,
                three_days_ago: three_days_ago
            });

            wx.stopPullDownRefresh();
        })
    },
    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        let _type = e.type;
        if(_type !== undefined){
            let from_user = e.detail.item;
            // 名片来源 1-扫描类型， 2-对方传递, 3-分享
            let _source_type = parseInt(from_user.type);
            // 名片完善情况,1-已完善, 2-待完善， 3-信息不全
            let _perfect = parseInt(from_user.from_user.perfect);

            switch(_source_type){
                case 1:
                    _url = './scan/index?id=' + from_user.id;
                    break;
                case 2:
                    _url = './impress/index?id=' + from_user.id;
                    break;
                case 3:
                    _url = './share/index?id=' + from_user.id;
                    break;
            }
            // if(_source_type === 1){
            //     // 扫描类型
            //     // 如果信息不全，可填写标记内容
            //     _url = _perfect > 1 ? './scan/unComplete/index?id=' + from_user.id : './scan/complete/index?id=' + from_user.id;
            //
            // }else if (_source_type === 2) {
            //     // 对方传递,
            //     _url = './impress/index?id=' + from_user.id
            // }
        }
        if(_url){
            wx.navigateTo({
                url: _url
            })
        }
    },

    // tab切换卡
    clickTab: function (e) {
        var that = this;
        if (this.data.currentTab === e.target.dataset.current) {
            return false;
        } else {
            that.setData({
                currentTab: e.target.dataset.current,
            });
            that.init();
        }
    },

    // 接受请求
    accept: function (e) {
        let id = e.detail.id;
        if(id > 0){
            util.post(api.ByAddingUrl, {id: id, is_adding: 1}).then(() => {
                wx.showToast({
                    title: '操作成功',
                    duration: 1500
                });
                setTimeout(() => {
                    this.init();
                }, 1500)
            });
        }
    }

})