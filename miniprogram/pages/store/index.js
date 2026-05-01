const api = require('../../config/api.js');
const util = require('../../utils/util.js');
var QQMapWX = require('../../qqmap/qqmap-wx-jssdk.js');
var qqmapsdk;
var baseUrl;
Page({

    /**
     * 页面的初始数据
     */
    data: {
        current_page: 1, // 当前页数
        last_page: 1, // 最后一页
        stores: [],
        next_page_url: '', // 下一页链接
        name: '', // 门店名称,
        city: ''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {

        util.getLocation().then(res => {
            baseUrl = api.StoreListUrl + '?latitude=' + res.latitude + '&longitude=' + res.longitude;
            this.setData({
                latitude: res.latitude, // 纬度
                longitude: res.longitude, // 经度,
                next_page_url: baseUrl,
            });
            this.getStores();
            this.getLocation();
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
        if (options && options.name) {
            this.setData({
                next_page_url: baseUrl + '&name=' + options.name,
                name: options.name
            });
            this.getStores();
        }
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
            next_page_url: this.data.name ? baseUrl + '&name=' + this.data.name : baseUrl,
            current_page: 1
        });
        this.getStores();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        let current_page = this.data.current_page + 1;
        this.setData({
            current_page: current_page
        });
        if (this.data.current_page > this.data.last_page) {
            return;
        }
        this.getStores();
    },

   

    getStores: function () {
        util.get(this.data.next_page_url).then(res => {
            let stores = [];
            if (this.data.current_page > 1) {
                stores = this.data.stores.concat(res.data.data);
            } else {
                stores = res.data.data;
            }
            this.setData({
                stores: stores,
                next_page_url: res.data.next_page_url,
                last_page: res.data.last_page
            });
            console.log(this.data.stores);
        });
    },
    getLocation: function () {
        util.get(api.GetConfigureUrl, {
            name: 'TENCENT_MAP_API_KEY',
            type: 'env'
        }).then(res => {
            let key = res.data.data.TENCENT_MAP_API_KEY;

            // 实例化API核心类
            qqmapsdk = new QQMapWX({
                key: key
            });
            qqmapsdk.reverseGeocoder({
                location: {
                    latitude: this.data.latitude,
                    longitude: this.data.longitude
                },
                success: (res) => {
                    this.setData({
                        city: res.result.address_component.city
                    })
                },
                fail: function (res) {
                    console.log(res)
                }
            })
        })
    },
    onSearch: function (event) {
        if (!this.data.name) {
            this.setData({
                next_page_url: baseUrl,
                current_page: 1
            });
            this.getStores();
            return;
        }

        this.setData({
            current_page: 1,
            stores: []
        });
        this.onShow({
            name: this.data.name
        })
    },
    changeName: function (event) {
        this.setData({
            name: event.detail.value
        })
    },
    navigateToUrl: function (event) {
        console.log(event);
        let url = event.currentTarget.dataset.url;
        wx.navigateTo({
            url: url
        });
    }
})