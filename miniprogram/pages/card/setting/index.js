// pages/card/setting/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        current: 0,
        industries: [
            [],
            []
        ], // 行业数据，picker组件显示
        industryArray: [], // 所有行业数据
        industry_index: [0, 0], // 默认行业选择索引,
        list: [{authorize: false}, {authorize: true}, {authorize: true}],

        userScreen: {},
        areasArray: [],
        is_show: false,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.getIndustries();
        this.getAreas()
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },
    getIndustries: function () {
        util.get(api.GetIndustriesUrl).then(res => {
            let response = res.data.data;
            response.unshift({
                id: 0,
                name: '不限',
                parent_id: 0,
                children: []
            });
            for (let [index, elem] of response.entries()) {
                elem.children.unshift({
                    id: elem.id + '-0',
                    name: '不限',
                    parent_id: elem.id
                })
            }

            let first_column = []; // 第一列数据
            let second_column = []; // 第二列数据
            let industries = this.data.industries;
            // 设置默认显示的数据
            if (response.length > 0) {
                first_column = response;
                if (response[0].children.length > 0) {
                    second_column = response[0].children;
                }
                industries[0] = first_column;
                industries[1] = second_column;
            }
            this.setData({
                industryArray: response,
                industries: industries,
            });
        })
    },
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        this.getCustomSearch();
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

    getCustomSearch: function(){
        util.get(api.GetCustomSearch,{}, false).then(response => {
            let userScreen = response.data.data.userScreen;
            this.setData({
                userScreen: userScreen,
                is_show: true,
            })
        })
    },
    getAreas: function(){
        wx.getStorage({
            key: 'AREAS_ARRAY',
            success: res => {
                let area_array = JSON.parse(res.data);
                this.setData({
                    areasArray: area_array,
                });
            },
            fail: () => {
                util.get(api.GetAreasUrl, {}, false).then(res => {
                    let response = res.data.data;
                    wx.setStorage({
                        key:"AREAS_ARRAY",
                        data:JSON.stringify(response)
                    });
                    this.setData({
                        areasArray: response,
                    });
                })
            }
        });
    },

    changeSearchItem(event){
        let item = event.detail;
        let index = event.currentTarget.dataset.index;
        this.data.userScreen[index] = item;
    },
    back(){
        const eventChannel = this.getOpenerEventChannel();

        //保存搜索配置到数据库
        util.post(api.StoreCustomSearch, {params: this.data.userScreen}, false);
        setTimeout(() => {
            eventChannel.emit('custom-search', this.data.userScreen);
            wx.navigateBack();
        }, 500);
    }
})