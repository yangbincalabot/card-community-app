const app = getApp();
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        orderId: '',
        pay_type: 1,
        passArray: [],
        bott: '',
        mask: false,
        orderDetail: {},
        balance: {},
        userInfo: {},
        application : {
            fee: '0.00',
        },
        reason: '',
        formData: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        const eventChannel = this.getOpenerEventChannel();
        eventChannel.on('application', data => {
            let application = data.association;
            let formData = data.formData;
            let reason = formData.reason;
            
            console.log(data);

            if(formData && formData.fee){
                formData.fee = Number(formData.fee).toFixed(2);
            }
            this.setData({ application, reason, formData })
            wx.setStorageSync('application', application);
            wx.setStorageSync('reaspon', reason);
            wx.setStorageSync('formData', formData);
        });

        setTimeout(() => {
            if(Object.keys(this.data.application).length === 0){
                let application = wx.getStorageSync('application');
                let reason = wx.getStorageSync('reason');
                let formData = wx.getStorageSync('formData');
                if(Object.keys(application).length > 0){
                    this.setData({ application, reason, formData })
                }else{
                    wx.navigateBack({
                        delta: 2
                    })
                }
                
            }
        }, 500);

    },


    getUserBalance: function () {
        let that = this;
        util.get(api.UserBalanceUrl)
            .then(response => {
                let _responseData = response.data.data;
                let _balance = _responseData.balance;
                console.log(_balance)
                if (_balance) {
                    _balance.money = Number(_balance.money);
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
                            wx.setStorageSync('society_pay_back', true);
                            wx.navigateTo({
                                url: '/pages/my/account/payPwd/index'
                            })
                        }
                    }
                });
                return false;
            }

            if (this.data.balance.money - this.data.formData.fee < 0) {
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
            util.post(api.ApplicationBalanceUrl, Object.assign(this.data.formData, { aid: that.data.application.id, cash_password: _cash_password, reason: this.data.reason }))
                .then(response => {
                    wx.redirectTo({
                        url: '../societyPaySuccess/societyPaySuccess?aid=' + this.data.application.id,
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
        this.getUserInfo();
        this.getUserBalance();
    },


    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            console.log(res.data.data);
            this.setData({
                userInfo: res.data.data
            });
        });
    },

    weChatPayPaymentParams: function () {
        let that = this;
        let _id = that.data.orderId;
        let _requestUrl = api.ApplicationWechatUrl;
        let _formParams = Object.assign(this.data.formData, { aid: this.data.application.id, reason: this.data.reason });
        util.post(_requestUrl, _formParams)
            .then(response => {
                let _responseData = response.data.data;
                console.log('支付参数:', _responseData);
                wx.showLoading({
                    title: '正在支付',
                });
                console.log(555);
                that.requestPay(_responseData);
                console.log(666);
            });
    },

    requestPay: function (_paymentParams) {
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
            'success': function (res) {
                console.log(333);
                console.log(res);
                if (res.errMsg == 'requestPayment:ok') {
                    wx.redirectTo({
                        url: '../societyPaySuccess/societyPaySuccess?aid=' + this.data.application.id,
                    });
                }
            },
            'complete': function (res) {
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