const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        userAddress: [],
        currentType:'',
        currentDefaultCheckedId:'',
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _chooseAddressData = wx.getStorageSync('chooseAddressData');
        if(_chooseAddressData){
            let _currentType = _chooseAddressData.choose_address_type;
            let _currentDefaultCheckedAddressId = _chooseAddressData.choose_address_id;
            that.setData({
                currentType:_currentType,
                currentDefaultCheckedId:_currentDefaultCheckedAddressId,
            });
        }
        // 检查是否是从需要选择收货地址的页面跳转过来的
        // 如果是，则需要在选中收货地址后会跳到来源地址
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
        this.getUserAddress();
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
        this.getUserAddress();
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },


    checkedAddress:function(e){
        let that = this;
        let _id = e.currentTarget.dataset.id;
        // 如果当前选中的地址id跟默认选中的地址id相同，则直接返回
        let _chooseAddressData = wx.getStorageSync('chooseAddressData');
        if(_chooseAddressData){
            let _currentType = _chooseAddressData.choose_address_type;
            that.setData({
                currentType:_currentType,
                currentDefaultCheckedId:_id,
            });
            _chooseAddressData.choose_address_id = _id;
            wx.setStorage({
                key: 'chooseAddressData',
                data: _chooseAddressData
            });
            switch (_currentType) {
                case "settlement":
                    wx.redirectTo({
                        url: '/pages/cart/settlement/index'
                    });
                    break;
                case "settlement_buy_now":
                    wx.redirectTo({
                        url: '/pages/cart/buy_now_settlement/index'
                    });
                    break;
            }
        }


    },

    backToFromPage:function(){

    },

    getUserAddress: function () {
        util.get(api.UserAddressListUrl).then(res => {
            console.log(res);
            this.setData({
                userAddress: res.data.data
            })
            wx.hideLoading();
        }).catch(error => {
            console.log(error)
        })
    },

    setDefaultAddress: function (event) {
        wx.showLoading({
            title: '修改中',
        });
        let id = event.currentTarget.dataset.id;

        util.post(api.UserAddressUpdateUrl, {
            id: id,
            is_default: true
        }).then(res => {
            wx.hideLoading();
            wx.showToast({
                title: '修改成功',
                icon: 'success',
                duration: 2000,
                success: () => {
                    setTimeout(() => {
                        this.onShow()
                    }, 1500)
                }
            });
        });
    },



    navigatorToUrl: function (e) {
        let _url = e.currentTarget.dataset.url;
        let _id = e.currentTarget.dataset.id;
        if (_url) {
            if(_id){
                _url = _url + '?id=' + _id;
            }
            wx.navigateTo({
                url: _url
            })
        }
    },

    // 删除
    deleteUserAddress: function (event) {
        wx.showLoading({
            title: '删除中',
        });
        let _id = event.currentTarget.dataset.id;
        util.post(api.UserAddressDeleteUrl, {
            id: _id
        }).then(res => {
            wx.hideLoading();
            wx.showToast({
                title: '删除成功',
                icon: 'success',
                duration: 2000,
                success: () => {
                    setTimeout(() => {
                        this.onShow();
                    }, 1500)
                }
            });
        });
    }
})