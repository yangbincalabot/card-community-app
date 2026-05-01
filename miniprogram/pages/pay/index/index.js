// pages/pay/index/index.js
const app = getApp();
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        orderId:'',
        pay_type: 1,
        passArray: [],
        bott: '',
        mask: false,
        orderDetail: {},
        balance: {},
        userInfo: {}
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        let _orderId = options.id;
        if(!_orderId){
            wx.showToast({ title: '不存在该订单，请退出重试', icon: 'none', duration: 800 });
            setTimeout(function () {
                wx.navigateBack({
                    delta:2
                })
            }, 800);
            return false;
        }
        that.getUserBalance();
        that.getOrderDetail(_orderId);
        console.log(_orderId);
        that.setData({
            orderId:_orderId
        });
    },

    getOrderDetail: function (_id) {
        let that = this;
        util.post(api.ApplyOrderDetail, {id: _id})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                if (!_data || _data.length === 0) {
                    that.prompt();
                    return false;
                }
                that.setData({
                    orderDetail: _data
                })
            });
    },

    getUserBalance: function () {
        let that = this;
        util.get(api.UserBalanceUrl)
            .then(response => {
                let _responseData = response.data.data;
                let _balance = _responseData.balance;
                console.log(_balance)
                if (_balance) {
                    that.setData({
                        balance: _balance
                    })
                }
            });
    },

    changeType: function (e) {
        let that = this;
        let _pay_type = e.currentTarget.dataset.type;
        if (parseInt(_pay_type) === 2) {
            let _set_cash = that.data.userInfo.set_cash;
            if (!_set_cash) {
                wx.showModal({
                    title: '设置密码',
                    content: '您当前还未设置支付密码，现在去设置支付密码吗',
                    success: function (res) {
                        if (res.confirm) {
                            wx.setStorageSync('apply_pay_back', true);
                            wx.navigateTo({
                                url: '/pages/my/account/payPwd/index'
                            })
                        }
                    }
                });
                return false;
            }
            let _balance_money = that.data.balance.money;
            let _order_price = that.data.orderDetail.price;
            if ((_balance_money - _order_price) < 0) {
                wx.showToast({ title: '余额不足，无法选择使用余额支付', icon: 'none', duration: 1000 });
                return false;
            }
        }
        that.setData({
            pay_type: _pay_type
        })
    },

    getPassword: function (e) {
        let that = this;
        let value = e.currentTarget.dataset.value;
        let _passArray = this.data.passArray;
        let _mask = true;
        let _bott = '';
        _passArray.push(parseInt(value));
        that.setData({
            passArray: _passArray,
        });
        if (_passArray.length === 6) {
            that.setData({
                passArray: _passArray
            });
            let _cash_password = _passArray.join('');
            util.post(api.ApplyBalancePayUrl,{id: that.data.orderId, cash_password:_cash_password})
                .then(response => {
                    // let _responseData = response.data.data;
                    // console.log(_responseData);
                    let _id = that.data.orderId;
                    wx.redirectTo({
                        url: '/pages/activity/apply/success/index?id=' + _id
                    });
                });
            that.setData({
                mask: false,
                passArray: []
            });
            return false;
        }
        console.log(_passArray);
        that.setData({
            mask: _mask,
            bott: _bott
        });
    },

    prompt: function () {
        wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
        setTimeout(function () {
            wx.navigateBack({
                delta: 1
            })
        }, 500);
    },

    reset: function () {
        let passArray = [];
        this.setData({
            passArray: passArray
        })
    },

    backspace: function () {
        let _passArray = this.data.passArray;
        _passArray.pop();
        this.setData({
            passArray: _passArray
        })
    },

    maskss: function () {
        this.setData({
            mask: false,
            bott: '',
            passArray: []
        })
    },

    payment: function () {
        let that = this;
        let _pay_type = that.data.pay_type;
        if (parseInt(_pay_type) === 2) {
            that.setData({
                mask: true
            });
            return false;
        }
        that.weChatPayPaymentParams();
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
        let that = this;
        that.getUserInfo();
    },

    getUserInfo: function () {
        let that = this;
        util.get(api.UserIndexUrl)
            .then(response => {
                let _data = response.data.data;
                if (_data) {
                    console.log(_data);
                    that.setData({
                        userInfo: _data
                    })
                }
            });
    },

    weChatPayPaymentParams:function(){
        let that = this;
        let _id = that.data.orderId;
        let _requestUrl = api.ApplyWechatPayUrl;
        let _formParams = {id:_id};
        util.post(_requestUrl,_formParams)
            .then(response => {
                let _responseData = response.data.data;
                wx.showLoading({
                    title: '正在支付',
                });
                console.log(555);
                that.requestPay(_responseData);
                console.log(666);
            });
    },

    requestPay:function(_paymentParams){
        console.log(111);
        let that = this;
        let _id = that.data.orderId;
        wx.hideLoading();
        wx.requestPayment({
            'timeStamp': _paymentParams.timeStamp,
            'nonceStr': _paymentParams.nonceStr,
            'package': _paymentParams.package,
            'signType': _paymentParams.signType,
            'paySign': _paymentParams.paySign,
            'success':function(res){
                console.log(333);
                console.log(res);
                if(res.errMsg == 'requestPayment:ok'){
                    wx.redirectTo({
                        url: '/pages/activity/apply/success/index?id=' + _id
                    });
                }
            },
            'complete':function(res){
                console.log(2222);
                wx.hideLoading();
            }
        })
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

});